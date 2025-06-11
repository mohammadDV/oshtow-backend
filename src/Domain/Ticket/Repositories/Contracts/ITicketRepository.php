<?php

namespace Domain\Ticket\Repositories\Contracts;

use Application\Api\Ticket\Requests\TicketRequest;
use Application\Api\Ticket\Requests\TicketStatusRequest;
use Core\Http\Requests\TableRequest;
use Domain\Ticket\models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

 /**
 * Interface ITicketRepository.
 */
interface ITicketRepository  {

    /**
     * Get the tikets pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Store the ticket.
     * @param TicketRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(TicketRequest $request) :JsonResponse;

    /**
     * Change status of the ticket
     * @param TicketStatusRequest $request
     * @param Ticket $ticket
     * @return JsonResponse
     */
    public function changeStatus(TicketStatusRequest $request, Ticket $ticket) :JsonResponse;

    /**
     * Get the sport.
     * @param Ticket $ticket
     * @return Ticket
     */
    public function show(Ticket $ticket) :Ticket;

}
