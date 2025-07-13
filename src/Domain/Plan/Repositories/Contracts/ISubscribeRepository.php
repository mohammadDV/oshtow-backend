<?php

namespace Domain\Plan\Repositories\Contracts;

use Application\Api\Plan\Requests\StoreSubscribeRequest;
use Core\Http\Requests\TableRequest;
use Domain\Plan\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Interface ISubscribeRepository.
 */
interface ISubscribeRepository
{
    /**
     * Get the plans pagination.
     * @param TableRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(TableRequest $request) :AnonymousResourceCollection;

    /**
     * Get the plans.
     * @return JsonResponse
     */
    public function activeSubscription() :JsonResponse;

    /**
     * Store the subscribtion.
     * @param StoreSubscribeRequest $request
     * @param Plan $plan
     * @throws \Exception
     */
    public function store(StoreSubscribeRequest $request, Plan $plan);
}