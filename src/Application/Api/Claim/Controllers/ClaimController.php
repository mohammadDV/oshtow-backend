<?php

namespace Application\Api\Claim\Controllers;

use Application\Api\Claim\Requests\ClaimRequest;
use Application\Api\Claim\Requests\ConfirmationRequest;
use Application\Api\Claim\Requests\ConfirmNewAmountRequest;
use Application\Api\Claim\Requests\DeliveryConfirmationRequest;
use Application\Api\Claim\Requests\SuggestNewAmountRequest;
use Application\Api\Payment\Requests\PaymentSecureRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Claim\Models\Claim;
use Domain\Claim\Repositories\Contracts\IClaimRepository;
use Domain\Project\Models\Project;
use Domain\User\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class ClaimController extends Controller
{

    /**
     * @param IClaimRepository $repository
     */
    public function __construct(protected IClaimRepository $repository)
    {

    }

    /**
     * Get all of projects with pagination
     * @param Project $project
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function getClaimsPerProject(Project $project, TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->getClaimsPerProject($project, $request), Response::HTTP_OK);
    }

    /**
     * Get all of projects with pagination
     * @param User $user
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function getClaimsPerUser(User $user, TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->getClaimsPerUser($user, $request), Response::HTTP_OK);
    }

    /**
     * Get the claim.
     * @param Claim $claim
     * @return JsonResponse
     */
    public function show(Claim $claim) :JsonResponse
    {
        return response()->json($this->repository->show($claim), Response::HTTP_OK);
    }

    /**
     * Get the status of the claim.
     * @param Claim $claim
     * @return JsonResponse
     */
    public function getStatus(Claim $claim) :JsonResponse
    {
        return response()->json($this->repository->getStatus($claim), Response::HTTP_OK);
    }

    /**
     * Store the claim.
     * @param ClaimRequest $request
     * @return JsonResponse
     */
    public function store(ClaimRequest $request) :JsonResponse
    {
        return $this->repository->store($request);
    }

    /**
     * Store the claim.
     * @param Claim $claim
     * @param ClaimRequest $request
     * @return JsonResponse
     */
    public function update(Claim $claim, ClaimRequest $request) :JsonResponse
    {
        return $this->repository->update($claim, $request);
    }

    /**
     * Approve the claim.
     * @param Claim $claim
     * @return JsonResponse
     */
    public function approveClaim(Claim $claim): JsonResponse
    {
        return $this->repository->approveClaim($claim);
    }

    /**
     * Approve the claim.
     * @param PaymentSecureRequest $request
     * @param Claim $claim
     * @return JsonResponse
     */
    public function paidClaim(PaymentSecureRequest $request, Claim $claim)
    {
        return $this->repository->paidClaim($request, $claim);
    }

    /**
     * Suggest new amount.
     * @param SuggestNewAmountRequest $request
     * @param Claim $claim
     * @return JsonResponse
     */
    public function suggestNewAmount(SuggestNewAmountRequest $request, Claim $claim)
    {
        return $this->repository->suggestNewAmount($request, $claim);
    }

    /**
     * Confirm new amount.
     * @param ConfirmNewAmountRequest $request
     * @param Claim $claim
     * @return JsonResponse
     */
    public function confirmNewAmount(ConfirmNewAmountRequest $request, Claim $claim)
    {
        return $this->repository->confirmNewAmount($request, $claim);
    }

    /**
     * Inprogress the claim.
     * @param Claim $claim
     * @param ConfirmationRequest $request
     * @return JsonResponse
     */
    public function inprogressClaim(Claim $claim, ConfirmationRequest $request): JsonResponse
    {
        return $this->repository->inprogressClaim($claim, $request);
    }

    /**
     * Delivery the claim.
     * @param Claim $claim
     * @param DeliveryConfirmationRequest $request
     * @return JsonResponse
     */
    public function deliveredClaim(Claim $claim, DeliveryConfirmationRequest $request): JsonResponse
    {
        return $this->repository->deliveredClaim($claim, $request);
    }

}
