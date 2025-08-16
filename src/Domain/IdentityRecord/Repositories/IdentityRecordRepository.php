<?php

namespace Domain\IdentityRecord\Repositories;

use Application\Api\IdentityRecord\Requests\ChangeStatusRequest;
use Application\Api\IdentityRecord\Requests\IdentityRecordRequest;
use Application\Api\IdentityRecord\Requests\UpdateIdentityRecordRequest;
use Carbon\Carbon;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\IdentityRecord\Models\IdentityRecord;
use Domain\IdentityRecord\Repositories\Contracts\IIdentityRecordRepository;
use Domain\Notification\Services\NotificationService;
use Domain\Payment\Models\Transaction;
use Domain\Plan\Models\Plan;
use Domain\Plan\Repositories\SubscribeRepository;
use Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Class IdentityRecordRepository.
 */
class IdentityRecordRepository implements IIdentityRecordRepository
{
    use GlobalFunc;

    /**
     * Get the identityRecords pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return IdentityRecord::query()
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('fullname', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhere('national_code', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the identityRecord.
     * @param IdentityRecord $identityRecord
     * @return ?IdentityRecord
     */
    public function show(IdentityRecord $identityRecord) : ?IdentityRecord
    {

        $this->checkLevelAccess(Auth::user()->id == $identityRecord->user_id);

        return IdentityRecord::query()
                ->where('id', $identityRecord->id)
                ->first();
    }

    /**
     * Get the identityRecord from the user.
     * @param User $user
     * @return ?IdentityRecord
     */
    public function getIdentityInfo(User $user) : ?IdentityRecord
    {

        $this->checkLevelAccess(Auth::user()->id == $user->id);

        return IdentityRecord::query()
                ->where('user_id', $user->id)
                ->first();
    }

    /**
     * Store the identityRecord.
     * @param IdentityRecordRequest $request
     * @throws \Exception
     */
    public function store(IdentityRecordRequest $request)
    {

        $exist = IdentityRecord::query()
            ->where('user_id', Auth::user()->id)
            ->first();

        if ($exist) {

            if ($exist->status == IdentityRecord::PENDING) {
                return $this->redirectToGateway($exist);
            }

            return response()->json([
                'status' => 0,
                'message' => __('site.Duplicate request')
            ], Response::HTTP_BAD_REQUEST);
        }

        $identityRecord = IdentityRecord::create([
            'first_name' => Auth::user()->first_name,
            'last_name' => Auth::user()->last_name,
            'country_id' => $request->input('country_id'),
            'province_id' => $request->input('province_id'),
            'city_id' => $request->input('city_id'),
            'national_code' => $request->input('national_code'),
            'mobile' => Auth::user()->mobile,
            'birthday' => $request->input('birthday'),
            'email' => Auth::user()->email,
            'postal_code' => $request->input('postal_code'),
            'address' => $request->input('address'),
            'image_national_code_front' => $request->input('image_national_code_front'),
            'image_national_code_back' => $request->input('image_national_code_back'),
            'video' => $request->input('video'),
            'status' => IdentityRecord::PENDING,
            'user_id' => Auth::user()->id,
        ]);

        if ($identityRecord) {

            Auth::user()->update([
                'address'               => $request->input('address'),
                'country_id'            => $request->input('country_id'),
                'province_id'           => $request->input('province_id'),
                'city_id'               => $request->input('city_id'),
            ]);

            return $this->redirectToGateway($identityRecord);
        }

        throw new \Exception();

    }

    /**
     * Redirect to Gateway.
     * @param IdentityRecord $identityRecord
     */
    public function redirectToGateway(IdentityRecord $identityRecord)
    {
        $plan = Plan::find(config('plan.default_plan_id'));

        $amount = intval($plan->amount);

        $transaction =  Transaction::create([
            'status' => Transaction::PENDING,
            'model_id' => $identityRecord->id,
            'model_type' => Transaction::IDENTITY,
            'amount' => $amount,
            'user_id' => Auth::user()->id,
        ]);

        $code = Transaction::generateHash($transaction->id);

        if ($transaction) {
            return [
                'status' => 1,
                'url' => route('user.payment') . '?transaction=' . $transaction->id . '&sign=' . $code
            ];
        }

        return [
            'status' => 0,
            'message' => __('site.request_not_found')
        ];
    }

    /**
     * Update the identityRecord.
     * @param ChangeStatusRequest $request
     * @param IdentityRecord $identityRecord
     * @return JsonResponse
     * @throws \Exception
     */
    public function changeStatus(ChangeStatusRequest $request, IdentityRecord $identityRecord) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->level == 3);

        if ($request->input('status') == IdentityRecord::COMPLETED) {

            try {
                DB::beginTransaction();
                $identityRecord->user->update([
                    'verified_at' => Carbon::now()
                ]);

                $identityRecord->update([
                    'status' => IdentityRecord::COMPLETED,
                ]);

                $user = User::find($identityRecord->user_id);

                // Add the default plan
                app(SubscribeRepository::class)->createSubscription(
                    Plan::find(config('plan.default_plan_id')), $user
                );

                NotificationService::create([
                    'title' => __('site.identity_verification_approved_title'),
                    'content' => __('site.identity_verification_approved_content'),
                    'id' => $user->id,
                    'type' => NotificationService::PROFILE,
                ], $user);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        if ($request->input('status') == IdentityRecord::REJECT) {
            $identityRecord->delete();
        }

        return response()->json([
            'status' => 1,
            'message' => __('site.The operation has been successfully')
        ], Response::HTTP_OK);
    }

    /**
     * Update the identityRecord.
     * @param int $identityRecordId
     */
    public function changeStatusToPaid(int $identityRecordId)
    {
        IdentityRecord::where('id', $identityRecordId)->update([
            'status' => IdentityRecord::PAID,
        ]);
    }

    /**
     * Update the identityRecord.
     * @param UpdateIdentityRecordRequest $request
     * @param IdentityRecord $identityRecord
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(UpdateIdentityRecordRequest $request, IdentityRecord $identityRecord) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->level == 3);

        $identityRecord = $identityRecord->update([
            'national_code' => $request->input('national_code'),
            'birthday' => $request->input('birthday'),
            'country' => $request->input('country'),
            'postal_code' => $request->input('postal_code'),
            'address' => $request->input('address'),
            'image_national_code_front' => $request->input('image_national_code_front'),
            'image_national_code_back' => $request->input('image_national_code_back'),
            'video' => $request->input('video'),
        ]);

        if ($identityRecord) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

     /**
    * Delete the identityRecord.
    * @param UpdatePasswordRequest $request
    * @param IdentityRecord $identityRecord
    * @return JsonResponse
    */
   public function destroy(IdentityRecord $identityRecord) :JsonResponse
   {
    $this->checkLevelAccess(Auth::user()->level == 3);

        $identityRecord->delete();

        if ($identityRecord) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
   }
}