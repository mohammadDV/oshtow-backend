<?php

namespace Domain\Review\Repositories\Contracts;

use Application\Api\Review\Requests\ReviewRequest;
use Core\Http\Requests\TableRequest;
use Domain\Claim\Models\Claim;
use Domain\Project\Models\Project;
use Domain\Review\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

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
     * Get the reviews per project pagination.
     * @param Project $project
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getReviewsPerUser(TableRequest $request, Project $project) :LengthAwarePaginator;

    /**
     * Get the review.
     * @param Review $review
     * @return Review
     */
    public function show(Review $review) :Review;

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