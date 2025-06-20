<?php

namespace Domain\Plan\Repositories\Contracts;

use Application\Api\Plan\Requests\PlanRequest;
use Core\Http\Requests\TableRequest;
use Domain\Plan\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IPlanRepository.
 */
interface IPlanRepository
{
    /**
     * Get the plans pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the plans.
     * @return Collection
     */
    public function activePlans() :Collection;

    /**
     * Get the plan.
     * @param Plan $plan
     * @return Plan
     */
    public function show(Plan $plan) :Plan;

    /**
     * Store the plan.
     * @param PlanRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(PlanRequest $request) :JsonResponse;

    /**
     * Update the plan.
     * @param PlanRequest $request
     * @param Plan $plan
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(PlanRequest $request, Plan $plan) :JsonResponse;

    /**
    * Delete the plan.
    * @param UpdatePasswordRequest $request
    * @param Plan $plan
    * @return JsonResponse
    */
   public function destroy(Plan $plan) :JsonResponse;
}
