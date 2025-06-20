<?php

namespace Domain\Ticket\Repositories\Contracts;

use Application\Api\Ticket\Requests\SubjectRequest;
use Core\Http\Requests\TableRequest;
use Domain\Ticket\Models\TicketSubject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

 /**
 * Interface ITicketSubjectRepository.
 */
interface ITicketSubjectRepository  {

    /**
     * Get the Subjects pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the Subjects.
     * @return Collection
     */
    public function activeSubjects() :Collection;

    /**
     * Get the subject.
     * @param TicketSubject $subject
     * @return TicketSubject
     */
    public function show(TicketSubject $subject) :TicketSubject;

    /**
     * Store the subject.
     * @param SubjectRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(SubjectRequest $request) :JsonResponse;

    /**
     * Update the subject.
     * @param SubjectRequest $request
     * @param TicketSubject $subject
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(SubjectRequest $request, TicketSubject $subject) :JsonResponse;

    /**
    * Delete the subject.
     * @param TicketSubject $subject
    * @return JsonResponse
    */
   public function destroy(TicketSubject $subject) :JsonResponse;

}
