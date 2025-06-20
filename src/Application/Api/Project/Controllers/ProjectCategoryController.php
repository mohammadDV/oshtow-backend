<?php

namespace Application\Api\Project\Controllers;

use Application\Api\Project\Requests\ProjectCategoryRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Project\Models\ProjectCategory;
use Domain\Project\Repositories\Contracts\IProjectCategoryRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class ProjectCategoryController extends Controller
{

    /**
     * @param IProjectCategoryRepository $repository
     */
    public function __construct(protected IProjectCategoryRepository $repository)
    {

    }

    /**
     * Get all of ProjectCategories with pagination
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function index(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->index($request), Response::HTTP_OK);
    }

    /**
     * Get all of ProjectCategories
     * @return JsonResponse
     */
    public function activeProjectCategories(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->activeProjectCategories($request), Response::HTTP_OK);
    }

    /**
     * Get the projectCategory.
     * @param
     * @return JsonResponse
     */
    public function show(ProjectCategory $projectCategory) :JsonResponse
    {
        return response()->json($this->repository->show($projectCategory), Response::HTTP_OK);
    }

    /**
     * Store the projectCategory.
     * @param ProjectCategoryRequest $request
     * @return JsonResponse
     */
    public function store(ProjectCategoryRequest $request) :JsonResponse
    {
        return $this->repository->store($request);
    }

    /**
     * Update the projectCategory.
     * @param ProjectCategoryRequest $request
     * @param ProjectCategory $projectCategory
     * @return JsonResponse
     */
    public function update(ProjectCategoryRequest $request, ProjectCategory $projectCategory) :JsonResponse
    {
        return $this->repository->update($request, $projectCategory);
    }

    /**
     * Delete the projectCategory.
     * @param ProjectCategory $projectCategory
     * @return JsonResponse
     */
    public function destroy(ProjectCategory $projectCategory) :JsonResponse
    {
        return $this->repository->destroy($projectCategory);
    }
}
