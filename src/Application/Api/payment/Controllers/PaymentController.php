<?php

namespace Application\Api\Payment\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\traits\GlobalFunc;
use Domain\Claim\Models\Claim;
use Domain\Claim\Repositories\Contracts\IClaimRepository;
use Domain\Payment\Models\PaymentSecure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    use GlobalFunc;

    public function __construct(
        protected IClaimRepository $claimRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function verifyPaymentSecure(Claim $claim): JsonResponse
    {

        $this->checkLevelAccess(
            in_array(
                Auth::user()->id,
                [$claim->user_id, $claim->project->user_id]
            ) && $claim->status == Claim::APPROVED
        );

        $this->claimRepository->paidClaim($claim);

        return response()->json([
            'status' => 1,
            'message' => __('site.The operation has been successfully'),
            'data' => $claim,
        ]);
    }


}
