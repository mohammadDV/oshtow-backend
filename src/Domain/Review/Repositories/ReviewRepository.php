<?php

namespace Domain\Review\Repositories;

use Application\Api\Review\Requests\ReviewRequest;
use Application\Api\Review\Resources\ReviewResource;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Claim\Models\Claim;
use Domain\Project\Models\Project;
use Domain\Review\Models\Review;
use Domain\Review\Repositories\Contracts\IReviewRepository;
use Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ReviewRepository.
 */
class ReviewRepository implements IReviewRepository
{
    use GlobalFunc;

    /**
     * Get the reviews pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $this->checkLevelAccess(Auth::user()->level == 3);

        $search = $request->get('query');
        return Review::query()
            ->with('user:id,nickname,profile_photo_path,rate')
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('comment', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the reviews per user pagination.
     * @param User $user
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getReviewsPerUser(TableRequest $request, User $user) :LengthAwarePaginator
    {

        $search = in_array($request->get('query'), [1,2,3,4,5]) ? $request->get('query') : 0;

        $reviews = Review::query()
            ->with('user:id,nickname,profile_photo_path,rate')
            ->where('owner_id', $user->id)
            ->where('status', 1)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('rate', $search);
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 10));

        return $reviews->through(fn ($review) => new ReviewResource($review));
    }

    /**
     * Get the review.
     * @param Review $review
     * @return ReviewResource
     */
    public function show(Review $review) :ReviewResource
    {
        $this->checkLevelAccess(Auth::user()->level == 3);

        $review = Review::query()
                ->where('id', $review->id)
                ->first();

        return new ReviewResource($review);
    }

    /**
     * Get the review per claim.
     * @param Claim $claim
     * @return Collection
     */
    public function getReviewsPerClaim(Claim $claim) :Collection
    {
        $this->checkLevelAccess(
            in_array(Auth::user()->id, [$claim->project->user_id, $claim->user_id])
        );

        $reviews = Review::query()
                ->with('user')
                ->where('claim_id', $claim->id)
                ->where('status', 1)
                ->get();

        return $reviews->map(fn ($review) => new ReviewResource($review));
    }

    /**
     * Store the review.
     * @param Claim $claim
     * @param ReviewRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Claim $claim, ReviewRequest $request) :JsonResponse
    {

        $this->checkLevelAccess(
            in_array(Auth::user()->id, [$claim->project->user_id, $claim->user_id]) &&
            $claim->status == Claim::DELIVERED
        );

        $owner = User::find(Auth::id() == $claim->user_id ? $claim->project->user_id : $claim->user_id);

        // Check for duplicate review
        $duplicate = Review::query()
            ->where('claim_id', $claim->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => 0,
                'message' => __('site.Duplicate review error'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        try {
            DB::beginTransaction();

            $review = Review::create([
                'comment' => $request->input('comment'),
                'rate' => $request->input('rate'),
                'claim_id' => $claim->id,
                'owner_id' => $owner->id,
                'user_id' => Auth::id(),
                'status' => 1,
            ]);

            $owner->update([
                'rate' => empty($owner->rate) ? $request->input('rate') : ceil((($owner->rate + $request->input('rate')) / 2))
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully'),
                'data' => $review
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }

    /**
     * Update the review.
     * @param ReviewRequest $request
     * @param Review $review
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(ReviewRequest $request, Review $review) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->level == 3);

        $review->update([
            'comment' => $request->input('comment'),
            'rate' => $request->input('rate'),
            'status' => !empty($request->input('status')) ? 1 : 0,
        ]);

        if ($review) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
    * Delete the review.
    * @param UpdatePasswordRequest $request
    * @param Review $review
    * @return JsonResponse
    */
   public function destroy(Review $review) :JsonResponse
   {
        $this->checkLevelAccess(Auth::user()->id == $review->user_id);

        $review->delete();

        if ($review) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
   }
}