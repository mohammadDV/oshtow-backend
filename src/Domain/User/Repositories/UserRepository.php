<?php

namespace Domain\User\Repositories;

use Application\Api\Project\Resources\ProjectResource;
use Application\Api\User\Requests\ChangePasswordRequest;
use Application\Api\User\Requests\UpdateUserRequest;
use Application\Api\User\Resources\UserResource;
use Carbon\Carbon;
use Core\Http\traits\GlobalFunc;
use Domain\Chat\Models\Chat;
use Domain\Chat\Models\ChatMessage;
use Domain\Claim\Models\Claim;
use Domain\IdentityRecord\Models\IdentityRecord;
use Domain\Plan\Models\Subscription;
use Domain\Project\Models\Project;
use Domain\Ticket\Models\Ticket;
use Domain\User\Models\User;
use Domain\User\Repositories\Contracts\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
     * @param User $user
     * @return array
     */
    public function getUserInfo(User $user) :array
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
            ->where('status', '!=', Project::REJECT)
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
            ->where('status', '!=', Project::REJECT)
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
     * Get the user info.
     * @param User $user
     * @return array
     */
    public function show(User $user) :array
    {
        $this->checkLevelAccess($user->id == Auth::user()->id);

        return [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'nickname' => $user->nickname,
                'address' => $user->address,
                'country_id' => $user->country_id,
                'province_id' => $user->province_id,
                'city_id' => $user->city_id,
                'mobile' => $user->mobile,
                'biography' => $user->biography,
                'profile_photo_path' => $user->profile_photo_path,
                'bg_photo_path' => $user->bg_photo_path,
                'rate' => $user->rate,
                'point' => $user->point,
            ]
        ];
    }

    /**
     * Get verification of the user
     * @return array
     */
    public function checkVerification() :array
    {
        $identityRecord = IdentityRecord::query()
            ->where('user_id', Auth::user()->id)
            ->first();

        $status = false;

        if ($identityRecord) {
            $status = $identityRecord->status;
        }

        return [
            'is_admin' => Auth::user()->level == 3,
            'verify_email' => !empty(Auth::user()->email_verified_at),
            'verify_access' => !empty(Auth::user()->verified_at),
            'status_approval' => $status,
            'user' => new UserResource(Auth::user())
        ];
    }

    /**
     * Get the dashboard info
     *
     * @return array The seller object
     */
    public function getDashboardInfo() :array
    {
        $senderCount = Project::query()
                ->where('user_id', Auth::user()->id)
                ->where('type', Project::SENDER)
                ->where('created_at', '>', Carbon::now()->subMonth())
                ->count();

        $passengerCount = Project::query()
                ->where('user_id', Auth::user()->id)
                ->where('type', Project::PASSENGER)
                ->where('created_at', '>', Carbon::now()->subMonth())
                ->count();

        $claimCount = Claim::query()
                ->where('user_id', Auth::user()->id)
                ->where('created_at', '>', Carbon::now()->subMonth())
                ->count();

        $receiveClaimCount = Claim::query()
                ->whereHas('project', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                })
                ->where('created_at', '>', Carbon::now()->subMonth())
                ->count();

        $ticketCount = Ticket::query()
                ->where('user_id', Auth::user()->id)
                ->where('created_at', '>', Carbon::now()->subMonth())
                ->count();

        $messageCount = ChatMessage::query()
                ->whereHas('chat', function ($query) {
                    return $query->where('user_id', Auth::user()->id)
                        ->orWhere('target_id', Auth::user()->id);
                })
                ->where('user_id', '!=', Auth::user()->id)
                ->where('created_at', '>', Carbon::now()->subMonth())
                ->count();

        return [
            'senders' => $senderCount,
            'passengers' => $passengerCount,
            'claims' => $claimCount,
            'receive_claims' => $receiveClaimCount,
            'tickets' => $ticketCount,
            'messages' => $messageCount,
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
                ->where('status', '!=', Project::REJECT)
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

     /**
     * Update the user.
     * @param UpdateUserRequest $request
     * @param User $user
     * @return array
     */
    public function update(UpdateUserRequest $request, User $user) :array
    {

        $this->checkLevelAccess($user->id == Auth::user()->id);

        // $role_id = !empty($request->input('role_id')) ? $request->input('role_id') : Auth::user()->role_id;

        // if ($user->id == Auth::user()->id) {
        //     $role_id = Auth::user()->role_id;
        // }

        // if ($role_id == 1 && Auth::user()->role_id != 1) {
        //     throw New \Exception('Unauthorized', 403);
        // }

        if (!$this->checkNickname($request->input('nickname'), $user->id)) {
            return [
                'status' => 0,
                'message' => __('site.The Nickname is invalid')
            ];
        }

        $update = $user->update([
            'first_name'            => $request->input('first_name'),
            'last_name'             => $request->input('last_name'),
            'nickname'              => $request->input('nickname'),
            'address'               => $request->input('address'),
            'country_id'            => $request->input('country_id'),
            'province_id'           => $request->input('province_id'),
            'city_id'               => $request->input('city_id'),
            'status'                => $user->level == 3 ? $request->input('status') : $user->status,
            // 'is_private'            => $request->input('is_private', false),
            'mobile'                => $request->input('mobile'),
            'biography'             => $request->input('biography'),
            'profile_photo_path'    => $request->input('profile_photo_path', config('image.default-profile-image')),
            'bg_photo_path'         => $request->input('bg_photo_path', config('image.default-background-image')),
        ]);

        if ($update) {

            // $role = Role::findOrFail($role_id);
            // $user->syncRoles([$role->name]);

            // $this->service->sendNotification(
            //     config('telegram.chat_id'),
            //     'ویرایش اطلاعات برای کاربر' . PHP_EOL .
            //     'first_name ' . $request->input('first_name') . PHP_EOL .
            //     'last_name ' . $request->input('last_name'),
            //     'nickname ' . $request->input('nickname'),
            //     'mobile ' . $request->input('mobile'),
            //     'national_code ' . $request->input('national_code'),
            //     'biography ' . $request->input('biography'),
            //     'profile_photo_path ' . $request->input('profile_photo_path', config('image.default-profile-image')),
            //     'bg_photo_path ' . $request->input('bg_photo_path', config('image.default-background-image')),
            // );

            return [
                'status' => 1,
                'message' => __('site.The data has been updated'),
                'user' => $user
            ];
        }

        throw new \Exception();
    }

    /**
     * Change the user password.
     * @param ChangePasswordRequest $request
     * @param User $user
     * @return array
     */
    public function changePassword(ChangePasswordRequest $request, User $user) :array
    {
        $this->checkLevelAccess($user->id == Auth::user()->id);

        // Verify current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return [
                'status' => 0,
                'message' => __('site.Current password is incorrect')
            ];
        }

        // Update password
        $update = $user->update([
            'password' => Hash::make($request->input('password'))
        ]);

        if ($update) {
            return [
                'status' => 1,
                'message' => __('site.Password has been changed successfully')
            ];
        }

        throw new \Exception();
    }
}
