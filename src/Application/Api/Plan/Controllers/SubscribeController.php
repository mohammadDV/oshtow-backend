<?php

namespace Application\Api\Plan\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Plan\Models\Plan;
use Domain\Plan\Repositories\Contracts\ISubscribeRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class SubscribeController extends Controller
{

    /**
     * @param ISubscribeRepository $repository
     */
    public function __construct(protected ISubscribeRepository $repository)
    {

    }

    /**
     * Get all of subscrptions with pagination
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function index(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->index($request), Response::HTTP_OK);
    }

    /**
     * Get the active subscrption
     * @return JsonResponse
     */
    public function activeSubscription(): JsonResponse
    {
        return $this->repository->activeSubscription();
    }

    /**
     * Store the subscribtion.
     * @param Plan $plan
     * @return JsonResponse
     */
    public function store(Plan $plan) :JsonResponse
    {
        return $this->repository->store($plan);
    }
}