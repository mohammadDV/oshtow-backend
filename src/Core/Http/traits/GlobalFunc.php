<?php

namespace Core\Http\traits;

use App\Services\Image\ImageService;
use Carbon\Carbon;
use Domain\Claim\Models\Claim;
use Domain\Plan\Models\Subscription;
use Domain\Project\Models\Project;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;

trait GlobalFunc
{
    /**
     * Check the level access
     * @param bool $conditions
     * @return void
     */
    public function checkLevelAccess(bool $condition = false) {

        if (!$condition && Auth::user()->level != 3) {
            throw New \Exception('Unauthorized', 403);
        }
    }

    /**
     * Expire the user's subscriprions
     * @return void
     */
    public function expireSubscriprions() {

        Subscription::query()
            ->where('user_id', Auth::user()->id)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', Carbon::now())
            ->update([
                'active' => 0,
                'ends_at' => Carbon::now()
            ]);
    }

    /**
     * Expire the user's subscriprions
     * @return void
     */
    public function checkSubscriprion($type = 'project') {

        $activeSubscription = Subscription::query()
            ->where('user_id', Auth::user()->id)
            ->where('active', 1)
            ->where('ends_at', '>', Carbon::now())
            ->first();

        if ($type == 'project' && $activeSubscription) {

            if ( $activeSubscription->project_count === 0) {
                return true;
            }

            return $activeSubscription->project_count > Project::query()
                ->where('user_id', Auth::user()->id)
                ->where('created_at', '>', $activeSubscription->created_at)
                ->count();
        }

        if ($type != 'project' && $activeSubscription) {

            if ( $activeSubscription->claim_count === 0) {
                return true;
            }

            return $activeSubscription->claim_count > Claim::query()
                ->where('user_id', Auth::user()->id)
                ->where('created_at', '>', $activeSubscription->created_at)
                ->count();
        }

        return false;
    }

    /**
     * Check the level access
     * @param bool $conditions
     * @return bool
     */
    public function checkNickname(string $nickname, int $userId = 0) : bool {

        if (User::query()
            ->where('nickname', $nickname)
            ->when(!empty($userId), function ($query) use($userId) {
                $query->where('id', '!=', $userId);
            })
            ->count() > 0) {
                return false;
        }

        return true;
    }

    /**
     * Check the level access
     * @param ImageService $imageService
     * @param $file
     * @param string $url
     * @param string $image
     * @return void
     */
    // public function uploadImage(ImageService $imageService, $file,string $url, $image){
    //     $imageService->setExclusiveDirectory($url);
    //     $result = $imageService->save($file);
    //     if ($result && !empty($image)){
    //         if(env('APP_ENV') == "production"){
    //             Storage::disk('s3')->delete($image);
    //         }else{
    //             $imageService->deleteImage($image);
    //         }
    //     }
    //     $imageService->reset();

    //     return $result;
    // }
}
;
