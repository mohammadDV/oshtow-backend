<?php

namespace Domain\Plan\Repositories\Contracts;

use Core\Http\Requests\TableRequest;
use Domain\Plan\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface ISubscribeRepository.
 */
interface ISubscribeRepository
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
    public function activeSubscription() :Collection;

    /**
     * Store the subscribtion.
     * @param Plan $plan
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Plan $plan) :JsonResponse;
}