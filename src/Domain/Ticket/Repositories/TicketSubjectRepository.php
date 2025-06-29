<?php

namespace Domain\Ticket\Repositories;

use Application\Api\Ticket\Requests\SubjectRequest;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Ticket\Models\TicketSubject;
use Domain\Ticket\Repositories\Contracts\ITicketSubjectRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TicketSubjectRepository implements ITicketSubjectRepository {

    use GlobalFunc;

    /**
     * Get the Subjects pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return TicketSubject::query()
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the Subjects.
     * @return Collection
     */
    public function activeSubjects() :Collection
    {
        return TicketSubject::query()
            ->where('status', 1)
            ->get();
    }

    /**
     * Get the subject.
     * @param TicketSubject $subject
     * @return TicketSubject
     */
    public function show(TicketSubject $subject) :TicketSubject
    {
        return $subject;
    }

    /**
     * Store the subject.
     * @param SubjectRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(SubjectRequest $request) :JsonResponse
    {
        $this->checkLevelAccess();

        $country = TicketSubject::create([
            'title'         => $request->input('title'),
            'user_id'       => Auth::user()->id,
            'status'        => $request->input('status'),
        ]);

        if ($country) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
    }

    /**
     * Update the subject.
     * @param SubjectRequest $request
     * @param TicketSubject $ticketSubject
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(SubjectRequest $request, TicketSubject $ticketSubject) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $ticketSubject->user_id);

        $ticketSubject = $ticketSubject->update([
            'title'         => $request->input('title'),
            'status'        => $request->input('status'),
        ]);

        if ($ticketSubject) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
    * Delete the subject.
    * @param UpdatePasswordRequest $request
    * @param TicketSubject $subject
    * @return JsonResponse
    */
   public function destroy(TicketSubject $subject) :JsonResponse
   {
        $this->checkLevelAccess(Auth::user()->id == $subject->user_id);

        $subject->delete();

        if ($subject) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
   }
}
