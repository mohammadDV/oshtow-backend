<?php

namespace Domain\Project\Repositories\Contracts;

use Application\Api\Project\Requests\ProjectCategoryRequest;
use Core\Http\Requests\TableRequest;
use Domain\Project\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IProjectCategoryRepository.
 */
interface IProjectCategoryRepository
{
    /**
     * Get the projectCategories pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the projectCategories.
     * @return Collection
     */
    public function activeProjectCategories() :Collection;

    /**
     * Get the projectCategory.
     * @param ProjectCategory $projectCategory
     * @return ProjectCategory
     */
    public function show(ProjectCategory $projectCategory) :ProjectCategory;

    /**
     * Store the projectCategory.
     * @param ProjectCategoryRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(ProjectCategoryRequest $request) :JsonResponse;

    /**
     * Update the projectCategory.
     * @param ProjectCategoryRequest $request
     * @param ProjectCategory $projectCategory
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(ProjectCategoryRequest $request, ProjectCategory $projectCategory) :JsonResponse;

    /**
    * Delete the projectCategory.
    * @param UpdatePasswordRequest $request
    * @param ProjectCategory $projectCategory
    * @return JsonResponse
    */
   public function destroy(ProjectCategory $projectCategory) :JsonResponse;
}
