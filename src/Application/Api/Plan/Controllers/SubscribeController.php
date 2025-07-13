<?php

namespace Application\Api\Plan\Controllers;

use Application\Api\Plan\Requests\StoreSubscribeRequest;
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
     * @param StoreSubscribeRequest $request
     * @param Plan $plan
     */
    public function store(StoreSubscribeRequest $request, Plan $plan)
    {
        return $this->repository->store($request, $plan);
    }
}