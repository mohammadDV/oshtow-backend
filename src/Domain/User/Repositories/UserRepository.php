<?php

namespace Domain\User\Repositories;

use Application\Api\Project\Resources\ProjectResource;
use Application\Api\User\Resources\UserResource;
use Carbon\Carbon;
use Core\Http\traits\GlobalFunc;
use Domain\Claim\Models\Claim;
use Domain\Plan\Models\Subscription;
use Domain\Project\Models\Project;
use Domain\User\Models\User;
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
     * Get the user info.
     * @param User $project
     * @return array
     */
    public function show(User $user) :array
    {

        $senderQuery = Project::query()
            ->with([
                'categories:id,title',
                'oCountry',
                'oProvince',
                'oCity',
                'dCountry',
                'dProvince',
                'dCity',
            ])
            ->where('user_id', $user->id)
            ->where('type', Project::SENDER)
            ->where('active', 1)
            ->orderBy('id', 'desc');

        $senderProjects = $senderQuery
            ->limit(4)
            ->get()
            ->map(fn ($project) => new ProjectResource($project));

        $senderProjectsCount = $senderQuery->count();

        $passengerQuery = Project::query()
            ->with([
                'categories:id,title',
                'oCountry',
                'oProvince',
                'oCity',
                'dCountry',
                'dProvince',
                'dCity',
            ])
            ->where('user_id', $user->id)
            ->where('type', Project::PASSENGER)
            ->where('active', 1)
            ->orderBy('id', 'desc');

        $passengerProjects = $passengerQuery
            ->limit(4)
            ->get()
            ->map(fn ($project) => new ProjectResource($project));

        $passengerProjectsCount = $passengerQuery->count();

        return [
            'user' => new UserResource($user),
            'sender_projects' => $senderProjects,
            'sender_projects_count' => $senderProjectsCount,
            'passenger_projects' => $passengerProjects,
            'passenger_projects_count' => $passengerProjectsCount
        ];
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
        $projects = 0;
        $claims = 0;
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