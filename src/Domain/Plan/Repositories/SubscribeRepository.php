<?php

namespace Domain\Plan\Repositories;

use Application\Api\Plan\Resources\SubscriptionResource;
use Carbon\Carbon;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Plan\Models\Plan;
use Domain\Plan\Models\Subscription;
use Domain\Plan\Repositories\Contracts\ISubscribeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class PlanRepository.
 */
class SubscribeRepository implements ISubscribeRepository
{
    use GlobalFunc;

    /**
     * Get the plans pagination.
     * @param TableRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(TableRequest $request) :AnonymousResourceCollection
    {
        $subscriptions = Subscription::query()
            ->with(['plan', 'user'])
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->orderBy($request->get('sortBy', 'id'), $request->get('sortType', 'desc'))
            ->paginate($request->get('rowsPerPage', 25));

        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Get the plans.
     * @return JsonResponse
     */
    public function activeSubscription() :JsonResponse
    {
        $subscription = Subscription::query()
            ->with(['plan', 'user'])
            ->where('user_id', Auth::user()->id)
            ->where('active', 1)
            ->first();

        if (!empty($subscription)) {
            return response()->json(new SubscriptionResource($subscription), Response::HTTP_OK);
        }

        return response()->json([
            'status' => 0,
            'message' => __('site.No active subscription found')
        ], Response::HTTP_NOT_FOUND);

    }

    /**
     * Store the subscription.
     * @param Plan $plan
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Plan $plan) :JsonResponse
    {
        Subscription::query()
            ->where('active', 1)
            ->where('user_id', Auth::user()->id)
            ->update([
                'active' => 0,
                'ends_at' => now()
            ]);

        $endsAt = Carbon::now()->addMonth(1 * $plan->period_count);

        if ($plan->priod == 'yearly') {
            $endsAt = Carbon::now()->addYear(1 * $plan->period_count);
        }

        $subscribe = Subscription::create([
            'ends_at' => $endsAt,
            'plan_id' => $plan->id,
            'user_id' => Auth::user()->id,
            'project_count' => $plan->project_count,
            'claim_count' => $plan->claim_count,
            'active' => 1
        ]);

        if ($subscribe) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
    }
}