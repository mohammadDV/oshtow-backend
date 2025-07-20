<?php

namespace Domain\Project\Repositories\Contracts;

use Application\Api\Project\Requests\ProjectRequest;
use Application\Api\Project\Requests\RejectProjectRequest;
use Application\Api\Project\Requests\SearchProjectRequest;
use Core\Http\Requests\TableRequest;
use Domain\Project\Models\Project;
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
     * Edit the project.
     * @param Project $project
     * @return Project
     */
    public function edit(Project $project) :Project;

    /**
     * Get the project.
     * @param Project $project
     * @return array
     */
    public function show(Project $project) :array;

    /**
     * Store the project.
     * @param ProjectRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(ProjectRequest $request) :JsonResponse;

    /**
     * Reject the project.
     * @param RejectProjectRequest $request
     * @param Project $project
     * @return JsonResponse
     * @throws \Exception
     */
    public function reject(RejectProjectRequest $request, Project $project) :JsonResponse;

    /**
     * Approve the project.
     * @param Project $project
     * @return JsonResponse
     * @throws \Exception
     */
    public function approve(Project $project) :JsonResponse;

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

    /**
     * Get featured projects by type with configurable limits.
     * @return array{sender: Collection, passenger: Collection}
     */
    public function getFeaturedProjects(): array;

    /**
     * Search projects with filters and pagination.
     * @param SearchProjectRequest $request
     * @return LengthAwarePaginator
     */
    public function search(SearchProjectRequest $request): LengthAwarePaginator;

    /**
     * Checking claim for the project.
     * @return array
     */
    public function checkRequestForClaim(Project $project): array;
}