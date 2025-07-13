<?php

namespace Domain\IdentityRecord\Repositories\Contracts;

use Application\Api\IdentityRecord\Requests\ChangeStatusRequest;
use Application\Api\IdentityRecord\Requests\IdentityRecordRequest;
use Application\Api\IdentityRecord\Requests\UpdateIdentityRecordRequest;
use Core\Http\Requests\TableRequest;
use Domain\IdentityRecord\Models\IdentityRecord;
use Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IIdentityRecordRepository.
 */
interface IIdentityRecordRepository
{
    /**
     * Get the identityRecords pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the identityRecord.
     * @param IdentityRecord $identityRecord
     * @return ?IdentityRecord
     */
    public function show(IdentityRecord $identityRecord) : ?IdentityRecord;

    /**
     * Get the identityRecord from the user.
     * @param User $user
     * @return ?IdentityRecord
     */
    public function getIdentityInfo(User $user) : ?IdentityRecord;

    /**
     * Store the identityRecord.
     * @param IdentityRecordRequest $request
     * @throws \Exception
     */
    public function store(IdentityRecordRequest $request);

    /**
     * Update the identityRecord.
     * @param UpdateIdentityRecordRequest $request
     * @param IdentityRecord $identityRecord
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(UpdateIdentityRecordRequest $request, IdentityRecord $identityRecord) :JsonResponse;

    /**
     * Change the status of identityRecord.
     * @param ChangeStatusRequest $request
     * @param IdentityRecord $identityRecord
     * @return JsonResponse
     * @throws \Exception
     */
    public function changeStatus(ChangeStatusRequest $request, IdentityRecord $identityRecord) :JsonResponse;

    /**
    * Delete the identityRecord.
    * @param UpdatePasswordRequest $request
    * @param IdentityRecord $identityRecord
    * @return JsonResponse
    */
   public function destroy(IdentityRecord $identityRecord) :JsonResponse;

}
