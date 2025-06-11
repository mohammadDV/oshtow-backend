<?php

namespace Domain\Project\Repositories\Contracts;

use Application\Api\Project\Requests\ProjectRequest;
use Core\Http\Requests\TableRequest;
use Domain\Project\models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IProjectRepository.
 */
interface IProjectRepository
{
    /**
     * Get the projects pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the active projects.
     * @return Collection
     */
    public function activeProjects() :Collection;

    /**
     * Get the project.
     * @param Project $project
     * @return Project
     */
    public function show(Project $project) :Project;

    /**
     * Store the project.
     * @param ProjectRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(ProjectRequest $request) :JsonResponse;

    /**
     * Update the project.
     * @param ProjectRequest $request
     * @param Project $project
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(ProjectRequest $request, Project $project) :JsonResponse;

    /**
     * Delete the project.
     * @param Project $project
     * @return JsonResponse
     */
    public function destroy(Project $project) :JsonResponse;
}