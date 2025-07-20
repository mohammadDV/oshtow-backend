<?php

namespace Application\Api\Project\Controllers;

use Application\Api\Project\Requests\ProjectRequest;
use Application\Api\Project\Requests\RejectProjectRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Project\Models\Project;
use Domain\Project\Models\ProjectCategory;
use Domain\Project\Repositories\Contracts\IProjectRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Application\Api\Project\Requests\SearchProjectRequest;


class ProjectController extends Controller
{

    /**
     * @param IProjectRepository $repository
     */
    public function __construct(protected IProjectRepository $repository)
    {

    }

    /**
     * Get all of projects with pagination
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function index(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->index($request), Response::HTTP_OK);
    }

    /**
     * Get the project.
     * @param Project $project
     * @return JsonResponse
     */
    public function show(Project $project) :JsonResponse
    {
        return response()->json($this->repository->show($project), Response::HTTP_OK);
    }

    /**
     * Edit the project.
     * @param Project $project
     * @return JsonResponse
     */
    public function edit(Project $project) :JsonResponse
    {
        return response()->json($this->repository->edit($project), Response::HTTP_OK);
    }

    /**
     * Store the project.
     * @param ProjectRequest $request
     * @return JsonResponse
     */
    public function store(ProjectRequest $request) :JsonResponse
    {
        return $this->repository->store($request);
    }

    /**
     * Update the project.
     * @param ProjectRequest $request
     * @param Project $project
     * @return JsonResponse
     */
    public function update(ProjectRequest $request, Project $project) :JsonResponse
    {
        return $this->repository->update($request, $project);
    }

    /**
     * Reject the project.
     * @param RejectProjectRequest $request
     * @param Project $project
     * @return JsonResponse
     */
    public function reject(RejectProjectRequest $request, Project $project) :JsonResponse
    {
        return $this->repository->reject($request, $project);
    }

    /**
     * Approve the project.
     * @param Project $project
     * @return JsonResponse
     */
    public function approve(Project $project) :JsonResponse
    {
        return $this->repository->approve($project);
    }

    /**
     * Delete the project.
     * @param Project $project
     * @return JsonResponse
     */
    public function destroy(Project $project) :JsonResponse
    {
        return $this->repository->destroy($project);
    }

    /**
     * Get featured projects by type.
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        return response()->json([
            'status' => 1,
            'data' => $this->repository->getFeaturedProjects()
        ], Response::HTTP_OK);
    }

    /**
     * Search projects with filters.
     * @param SearchProjectRequest $request
     * @return JsonResponse
     */
    public function search(SearchProjectRequest $request): JsonResponse
    {
        return response()->json($this->repository->search($request), Response::HTTP_OK);
    }

    /**
     * Check request for claiming
     * @param Project $project
     * @return JsonResponse
     */
    public function checkRequestForClaim(Project $project): JsonResponse
    {
        return response()->json($this->repository->checkRequestForClaim($project), Response::HTTP_OK);
    }
}
