<?php

namespace Application\Api\Ticket\Controllers;

use Application\Api\Ticket\Requests\SubjectRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Ticket\Models\TicketSubject;
use Domain\Ticket\Repositories\Contracts\ITicketSubjectRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TicketSubjectController extends Controller
{
    /**
     * Constructor of TicketSubjectController.
     */
    public function __construct(protected  ITicketSubjectRepository $repository)
    {
        //
    }

    /**
     * Get all of Subjects with pagination
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function index(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->index($request), Response::HTTP_OK);
    }

    /**
     * Get all of Subjects
     * @return JsonResponse
     */
    public function activeSubjects(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->activeSubjects($request), Response::HTTP_OK);
    }

    /**
     * Get the subject.
     * @param TicketSubject $subject
     * @return JsonResponse
     */
    public function show(TicketSubject $ticketSubject) :JsonResponse
    {
        return response()->json($this->repository->show($ticketSubject), Response::HTTP_OK);
    }

    /**
     * Store the subject.
     * @param SubjectRequest $request
     * @return JsonResponse
     */
    public function store(SubjectRequest $request) :JsonResponse
    {
        return $this->repository->store($request);
    }

    /**
     * Update the subject.
     * @param SubjectRequest $request
     * @param TicketSubject $ticketSubject
     * @return JsonResponse
     */
    public function update(SubjectRequest $request, TicketSubject $ticketSubject) :JsonResponse
    {
        return $this->repository->update($request, $ticketSubject);
    }

    /**
     * Delete the subject.
     * @param TicketSubject $subject
     * @return JsonResponse
     */
    public function destroy(TicketSubject $ticketSubject) :JsonResponse
    {
        return $this->repository->destroy($ticketSubject);
    }
}
