<?php

namespace Domain\Review\Repositories\Contracts;

use Application\Api\Review\Requests\ReviewRequest;
use Application\Api\Review\Resources\ReviewResource;
use Core\Http\Requests\TableRequest;
use Domain\Claim\Models\Claim;
use Domain\Review\Models\Review;
use Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface IReviewRepository.
 */
interface IReviewRepository
{
    /**
     * Get the reviews pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the reviews per user pagination.
     * @param User $user
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getReviewsPerUser(TableRequest $request, User $user) :LengthAwarePaginator;

    /**
     * Get the review.
     * @param Review $review
     * @return ReviewResource
     */
    public function show(Review $review) :ReviewResource;

    /**
     * Get the review per claim.
     * @param Claim $claim
     * @return Collection
     */
    public function getReviewsPerClaim(Claim $claim) :Collection;

    /**
     * Store the review.
     * @param Claim $claim
     * @param ReviewRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Claim $claim, ReviewRequest $request) :JsonResponse;

    /**
     * Update the review.
     * @param ReviewRequest $request
     * @param Review $review
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(ReviewRequest $request, Review $review) :JsonResponse;

    /**
    * Delete the review.
    * @param UpdatePasswordRequest $request
    * @param Review $review
    * @return JsonResponse
    */
   public function destroy(Review $review) :JsonResponse;
}