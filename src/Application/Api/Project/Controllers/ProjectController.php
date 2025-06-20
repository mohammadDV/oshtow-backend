<?php

namespace Application\Api\Project\Controllers;

use Application\Api\Project\Requests\ProjectRequest;
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
     * Get all of projects
     * @return JsonResponse
     */
    public function activeProjects(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->activeProjects($request), Response::HTTP_OK);
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
}
