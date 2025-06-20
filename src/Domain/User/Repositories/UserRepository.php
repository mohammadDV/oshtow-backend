<?php

namespace Domain\User\Repositories;

use Carbon\Carbon;
use Core\Http\traits\GlobalFunc;
use Domain\Claim\Models\Claim;
use Domain\Plan\Models\Subscription;
use Domain\Project\Models\Project;
use Domain\User\Repositories\Contracts\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserRepository.
 */
class UserRepository implements IUserRepository
{

    use GlobalFunc;

    /**
     * Get the users
     *
     * @return JsonResponse The seller object
     */
    public function index() :JsonResponse
    {
        return response()->json([]);
    }

    /**
     * Get Activity Count about the user
     *
     * @return JsonResponse The seller object
     */
    public function getActivityCount() :JsonResponse
    {
        // Expire the old subscription
        $this->expireSubscriprions();

        // Get active subscription
        $activeSubscription = Subscription::query()
            ->where('user_id', Auth::user()->id)
            ->where('active', 1)
            ->where('ends_at', '>', Carbon::now())
            ->first();

        $projectCount = 0;
        $claimCount = 0;
        $subscriptionInfo = null;

        if ($activeSubscription) {

            // Get projects count
            $projectCount = Project::query()
                ->where('user_id', Auth::user()->id)
                ->where('created_at', '>', $activeSubscription->created_at)
                ->count();

            // Get claims count
            $claimCount = Claim::query()
                ->where('user_id', Auth::user()->id)
                ->where('created_at', '>', $activeSubscription->created_at)
                ->count();

            $projects = $activeSubscription->project_count;
            $claims = $activeSubscription->claim_count;

            $remainingDays = now()->diffInDays($activeSubscription->ends_at, false);
            $subscriptionInfo = [
                'has_active_subscription' => 1,
                'remaining_days' => $remainingDays,
                'ends_at' => $activeSubscription->ends_at,
                'message' => $remainingDays > 0
                    ? __('site.:days days remain to expire your subscription', ['days' => $remainingDays])
                    : __('site.Your subscription has expired')
            ];
        } else {
            $subscriptionInfo = [
                'has_active_subscription' => 0,
                'remaining_days' => 0,
                'ends_at' => null,
                'message' => __('site.No active subscription found')
            ];
        }

        return response()->json([
            'project_count' => $projectCount,
            'projects' => $projects,
            'claim_count' => $claimCount,
            'claims' => $claims,
            'subscription' => $subscriptionInfo
        ]);
    }
}