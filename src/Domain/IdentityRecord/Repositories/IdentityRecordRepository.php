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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
     * @return IdentityRecord
     */
    public function show(IdentityRecord $identityRecord) :IdentityRecord
    {

        $this->checkLevelAccess(Auth::user()->id == $identityRecord->user_id);

        return IdentityRecord::query()
                ->where('id', $identityRecord->id)
                ->first();
    }

    /**
     * Store the identityRecord.
     * @param IdentityRecordRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(IdentityRecordRequest $request) :JsonResponse
    {
        $this->checkLevelAccess();

        $identityRecord = IdentityRecord::create([
            'fullname' => $request->input('fullname'),
            'national_code' => $request->input('national_code'),
            'mobile' => $request->input('mobile'),
            'birthday' => $request->input('birthday'),
            'email' => $request->input('email'),
            'country' => $request->input('country'),
            'postal_code' => $request->input('postal_code'),
            'address' => $request->input('address'),
            'image_national_code_front' => $request->input('image_national_code_front'),
            'image_national_code_back' => $request->input('image_national_code_back'),
            'video' => $request->input('video'),
            'status' => IdentityRecord::PENDING,
            'user_id' => Auth::user()->id,
        ]);

        if ($identityRecord) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
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

            $identityRecord->user->update([
                'verified_at' => Carbon::now()
            ]);

            $identityRecord->update([
                'status' => IdentityRecord::COMPLETED,
            ]);
        }

        if ($request->input('status') == IdentityRecord::REJECT) {
            $identityRecord->delete();
        }

        if ($identityRecord) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
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
            'fullname' => $request->input('fullname'),
            'national_code' => $request->input('national_code'),
            'mobile' => $request->input('mobile'),
            'birthday' => $request->input('birthday'),
            'email' => $request->input('email'),
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