<?php

namespace Application\Api\Review\Controllers;

use Application\Api\Review\Requests\ReviewRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Claim\Models\Claim;
use Domain\Project\Models\Project;
use Domain\Review\Models\Review;
use Domain\Review\Repositories\Contracts\IReviewRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class ReviewController extends Controller
{

    /**
     * @param IReviewRepository $repository
     */
    public function __construct(protected IReviewRepository $repository)
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
     * Get the reviews per project pagination.
     * @param TableRequest $request
     * @param Project $project
     * @return JsonResponse
     */
    public function getReviewsPerUser(TableRequest $request, Project $project): JsonResponse
    {
        return response()->json($this->repository->getReviewsPerUser($request, $project), Response::HTTP_OK);
    }

    /**
     * Get the review.
     * @param
     * @return JsonResponse
     */
    public function show(Review $review) :JsonResponse
    {
        return response()->json($this->repository->show($review), Response::HTTP_OK);
    }

    /**
     * Store the review.
     * @param Claim $claim
     * @param ReviewRequest $request
     * @return JsonResponse
     */
    public function store(Claim $claim, ReviewRequest $request) :JsonResponse
    {
        return $this->repository->store($claim, $request);
    }

    /**
     * Update the review.
     * @param ReviewRequest $request
     * @param Review $review
     * @return JsonResponse
     */
    public function update(ReviewRequest $request, Review $review) :JsonResponse
    {
        return $this->repository->update($request, $review);
    }

    /**
     * Delete the review.
     * @param Review $review
     * @return JsonResponse
     */
    public function destroy(Review $review) :JsonResponse
    {
        return $this->repository->destroy($review);
    }
}