<?php

namespace Domain\Plan\Repositories;

use Application\Api\Plan\Requests\PlanRequest;
use Carbon\Carbon;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Plan\Models\Plan;
use Domain\Plan\Models\Subscription;
use Domain\Plan\Repositories\Contracts\ISubscribeRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class PlanRepository.
 */
class SubscribeRepository implements ISubscribeRepository
{
    use GlobalFunc;

    /**
     * Get the plans pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        return Subscription::query()
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->orderBy($request->get('sortBy', 'id'), $request->get('sortType', 'desc'))
            ->paginate($request->get('rowsPerPage', 25));
    }

    /**
     * Get the plans.
     * @return Collection
     */
    public function activeSubscription() :Collection
    {
        return Plan::query()
            ->where('status', 1)
            ->get();
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
