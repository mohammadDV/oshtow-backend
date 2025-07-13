<?php

namespace Application\Api\IdentityRecord\Controllers;

use Application\Api\IdentityRecord\Requests\ChangeStatusRequest;
use Application\Api\IdentityRecord\Requests\IdentityRecordRequest;
use Application\Api\IdentityRecord\Requests\UpdateIdentityRecordRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\IdentityRecord\Models\IdentityRecord;
use Domain\IdentityRecord\Repositories\Contracts\IIdentityRecordRepository;
use Domain\User\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class IdentityRecordController extends Controller
{

    /**
     * @param IIdentityRecordRepository $repository
     */
    public function __construct(protected IIdentityRecordRepository $repository)
    {

    }

    /**
     * Get all of identityRecords with pagination
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function index(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->index($request), Response::HTTP_OK);
    }

    /**
     * Get the identityRecord.
     * @param IdentityRecord $identityRecord
     * @return JsonResponse
     */
    public function show(IdentityRecord $identityRecord) :JsonResponse
    {
        return response()->json($this->repository->show($identityRecord), Response::HTTP_OK);
    }

    /**
     * Get the identityRecord from the user.
     * @param User $user
     * @return JsonResponse
     */
    public function getIdentityInfo(User $user) :JsonResponse
    {
        return response()->json($this->repository->getIdentityInfo($user), Response::HTTP_OK);
    }

    /**
     * Store the identityRecord.
     * @param IdentityRecordRequest $request
     */
    public function store(IdentityRecordRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Update the identityRecord.
     * @param UpdateIdentityRecordRequest $request
     * @param IdentityRecord $identityRecord
     * @return JsonResponse
     */
    public function update(UpdateIdentityRecordRequest $request, IdentityRecord $identityRecord) :JsonResponse
    {
        return $this->repository->update($request, $identityRecord);
    }

    /**
     * Change the status of identityRecord.
     * @param ChangeStatusRequest $request
     * @param IdentityRecord $identityRecord
     * @return JsonResponse
     */
    public function changeStatus(ChangeStatusRequest $request, IdentityRecord $identityRecord) :JsonResponse
    {
        return $this->repository->changeStatus($request, $identityRecord);
    }

    /**
     * Delete the identityRecord.
     * @param IdentityRecord $identityRecord
     * @return JsonResponse
     */
    public function destroy(IdentityRecord $identityRecord) :JsonResponse
    {
        return $this->repository->destroy($identityRecord);
    }
}
