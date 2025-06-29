<?php

namespace Domain\Ticket\Repositories;

use Application\Api\Ticket\Requests\TicketMessageRequest;
use Application\Api\Ticket\Requests\TicketRequest;
use Application\Api\Ticket\Requests\TicketStatusRequest;
use Carbon\Carbon;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Ticket\Models\Ticket;
use Domain\Ticket\Models\TicketMessage;
use Domain\Ticket\Repositories\Contracts\ITicketRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TicketRepository implements ITicketRepository {

    use GlobalFunc;

    /**
     * Get the tickets pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return Ticket::query()
            ->with('subject')
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                // return $query->where('title', 'like', '%' . $search . '%')
                //     ->orWhere('alias_title','like','%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the sport.
     * @param Ticket $ticket
     * @return Ticket
     */
    public function show(Ticket $ticket) :Ticket
    {
        return Ticket::query()
                ->with('subject')
                ->with('messages')
                ->where('id', $ticket->id)
                ->first();
    }

    /**
     * Store the ticket.
     * @param TicketRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(TicketRequest $request) :JsonResponse
    {

        $query = Ticket::query()
            ->where('user_id', Auth::user()->id)
            ->where('status', Ticket::STATUS_ACTIVE)
            ->orderBy('id', 'desc');

        $createdAt = Carbon::parse($query->first()?->created_at);

        // Check if created_at is more than 5 minutes ago
        if ($createdAt->diffInMinutes(Carbon::now()) < config('times.ticket_time_min')) {
            return response()->json([
                'status' => 0,
                'message' => __('site.You are not allowed to resend messages. Please try again in 5 minutes.', ['number' => config('times.ticket_time_min')])
            ], Response::HTTP_CREATED);
        }

        // Check if created_at is more than 5 minutes ago
        if ($query->count() > 2) {
            return response()->json([
                'status' => 0,
                'message' => __('site.You are not allowed to send new tickets because you have 3 active tickets.', ['number' => config('times.ticket_time_min')])
            ], Response::HTTP_CREATED);
        }

        // Check if created_at is more than 5 minutes ago
        if ($createdAt->diffInMinutes(Carbon::now()) < config('times.ticket_time_min')) {
            return response()->json([
                'status' => 0,
                'message' => __('site.You are not allowed to resend messages. Please try again in 5 minutes.')
            ], Response::HTTP_CREATED);
        }

        $ticket = Ticket::create([
            'subject_id'    => $request->input('subject_id'),
            'user_id'       => Auth::user()->id,
        ]);

        $message = TicketMessage::create([
            'ticket_id'    => $ticket->id,
            'file'         => $request->input('file', null),
            'message'      => $request->input('message'),
            'user_id'      => Auth::user()->id,
        ]);

        if ($message) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
    }

    /**
     * Change status of the ticket
     * @param TicketStatusRequest $request
     * @param Ticket $ticket
     * @return JsonResponse
     */
    public function changeStatus(TicketStatusRequest $request, Ticket $ticket) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $ticket->user_id);

        if (Auth::user()->level != 3) {
            $update = $ticket->update([
                'status' => Ticket::STATUS_CLOSED
            ]);
        } else {
            $update = $ticket->update([
                'status' => $request->input('status')
            ]);
        }


        if ($update) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
     * Store the message of ticket.
     * @param TicketMessageRequest $request
     * @param Ticket $ticket
     * @return JsonResponse
     * @throws \Exception
     */
    public function storeMessage(TicketMessageRequest $request, Ticket $ticket) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $ticket->user_id);

        $exist = TicketMessage::query()
                    ->where('ticket_id', $ticket->id)
                    ->orderBy('id', 'desc')
                    ->first();

        if ($exist->user_id == Auth::user()->id && Auth::user()->level != 3) {
            return response()->json([
                'status' => 0,
                'message' => __('site.You are not allowed to resend messages. Please wait until the operator answers.')
            ], Response::HTTP_OK);
        }

        $message = TicketMessage::create([
            'ticket_id'    => $ticket->id,
            'file'         => $request->input('file', null),
            'message'      => $request->input('message'),
            'user_id'      => Auth::user()->id,
        ]);

        if ($message) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
    * Delete the ticket.
    * @param Ticket $ticket
    * @return JsonResponse
    */
   public function destroy(Ticket $ticket) :JsonResponse
   {
        $this->checkLevelAccess(Auth::user()->id == $ticket->user_id);

        $ticket->delete();

        if ($ticket) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
   }
}
