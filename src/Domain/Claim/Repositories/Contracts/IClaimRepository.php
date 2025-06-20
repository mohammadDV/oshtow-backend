<?php

namespace Domain\Claim\Repositories\Contracts;

use Application\Api\Claim\Requests\ClaimRequest;
use Application\Api\Claim\Requests\ConfirmationRequest;
use Application\Api\Claim\Requests\DeliveryConfirmationRequest;
use Core\Http\Requests\TableRequest;
use Domain\Claim\Models\Claim;
use Domain\Project\Models\Project;
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
     * Store a new claim.
     * @param ClaimRequest $request
     * @return JsonResponse
     */
    public function store(ClaimRequest $request): JsonResponse;

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
     * @param Claim $claim
     * @return void
     */
    public function paidClaim(Claim $claim): void;

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