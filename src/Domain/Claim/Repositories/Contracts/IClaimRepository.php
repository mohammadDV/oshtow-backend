<?php

namespace Domain\Claim\Repositories\Contracts;

use Application\Api\Claim\Requests\ClaimRequest;
use Application\Api\Claim\Requests\ConfirmationRequest;
use Application\Api\Claim\Requests\DeliveryConfirmationRequest;
use Application\Api\Payment\Requests\PaymentSecureRequest;
use Core\Http\Requests\TableRequest;
use Domain\Claim\Models\Claim;
use Domain\Project\Models\Project;
use Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IClaimRepository.
 */
interface IClaimRepository
{
    /**
     * Get all claims for a specific project.
     * @param Project $project
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getClaimsPerProject(Project $project, TableRequest $request): LengthAwarePaginator;

    /**
     * Get all claims for a specific user.
     * @param User $user
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getClaimsPerUser(User $user, TableRequest $request): LengthAwarePaginator;

    /**
     * Store a new claim.
     * @param ClaimRequest $request
     * @return JsonResponse
     */
    public function store(ClaimRequest $request): JsonResponse;

    /**
     * Get the status of the claim.
     * @param Claim $claim
     * @return array
     */
    public function getStatus(Claim $claim): array;

    /**
     * Store a new claim.
     * @param ClaimRequest $request
     * @return JsonResponse
     */
    public function update(Claim $claim, ClaimRequest $request): JsonResponse;

    /**
     * Approve a claim.
     * @param Claim $claim
     * @return JsonResponse
     */
    public function approveClaim(Claim $claim): JsonResponse;

    /**
     * Paid a claim.
     * @param PaymentSecureRequest $request
     * @param Claim $claim
     */
    public function paidClaim(PaymentSecureRequest $request, Claim $claim);

    /**
     * Get the claim.
     * @param Claim $claim
     * @return Claim
     */
    public function show(Claim $claim) :Claim;

    /**
     * Inprogress a claim.
     * @param Claim $claim
     * @param ConfirmationRequest $request
     * @return JsonResponse
     */
    public function inprogressClaim(Claim $claim, ConfirmationRequest $request): JsonResponse;

    /**
     * Delivery a claim.
     * @param Claim $claim
     * @param DeliveryConfirmationRequest $request
     * @return JsonResponse
     */
    public function deliveredClaim(Claim $claim, DeliveryConfirmationRequest $request): JsonResponse;
}
