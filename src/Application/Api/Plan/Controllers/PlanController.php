<?php

namespace Application\Api\Plan\Controllers;

use Application\Api\Plan\Requests\PlanRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Plan\Models\Plan;
use Domain\Plan\Repositories\Contracts\IPlanRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class PlanController extends Controller
{

    /**
     * @param IPlanRepository $repository
     */
    public function __construct(protected IPlanRepository $repository)
    {

    }

    /**
     * Get all of Countries with pagination
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function index(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->index($request), Response::HTTP_OK);
    }

    /**
     * Get all active plans
     * @return JsonResponse
     */
    public function activePlans(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->activePlans($request), Response::HTTP_OK);
    }

    /**
     * Get the plan.
     * @param
     * @return JsonResponse
     */
    public function show(Plan $plan) :JsonResponse
    {
        return response()->json($this->repository->show($plan), Response::HTTP_OK);
    }

    /**
     * Store the plan.
     * @param PlanRequest $request
     * @return JsonResponse
     */
    public function store(PlanRequest $request) :JsonResponse
    {
        return $this->repository->store($request);
    }

    /**
     * Update the plan.
     * @param PlanRequest $request
     * @param Plan $plan
     * @return JsonResponse
     */
    public function update(PlanRequest $request, Plan $plan) :JsonResponse
    {
        return $this->repository->update($request, $plan);
    }

    /**
     * Delete the plan.
     * @param Plan $plan
     * @return JsonResponse
     */
    public function destroy(Plan $plan) :JsonResponse
    {
        return $this->repository->destroy($plan);
    }
}