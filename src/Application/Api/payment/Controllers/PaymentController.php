<?php

namespace Application\Api\Payment\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\traits\GlobalFunc;
use Domain\Claim\Models\Claim;
use Domain\Claim\Repositories\Contracts\IClaimRepository;
use Domain\IdentityRecord\Repositories\IdentityRecordRepository;
use Domain\Payment\Models\Transaction;
use Domain\Plan\Repositories\SubscribeRepository;
use Domain\User\Models\User;
use Domain\Wallet\Repositories\WalletRepository;
use Evryn\LaravelToman\CallbackRequest;
use Evryn\LaravelToman\Facades\Toman;
use Illuminate\Http\JsonResponse;
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
    public function payment()
    {
        $amount = 1000;

        $request = Toman::amount(1000)
            ->description('Subscribing to Plan A')
            ->callback(route('user.payment.callback'))
            ->mobile('09350000000')
            ->email('amirreza@example.com')
            ->request();

        if ($request->successful()) {

            Transaction::create([
                'bank_transaction_id' => $request->transactionId(),
                'status' => Transaction::PENDING,
                'model_id' => 14,
                'model_type' => Transaction::WALLET,
                'amount' => $amount,
                'user_id' => 10,
            ]);

            return $request->pay(); // Redirect to payment URL
        }

        if ($request->failed()) {
            // Handle transaction request failure; Probably showing proper error to user.
        }
    }

    /**
    * Handle payment callback
    */
    public function callback(CallbackRequest $request)
    {

        $transaction = Transaction::where('bank_transaction_id', $request->transactionId())->first();

        if ($transaction) {

            $payment = $request->amount($transaction->amount)->verify();

            if ($payment->successful()) {
                // Store the successful transaction details
                $referenceId = $payment->referenceId();

                $transaction->update([
                    'reference' => $referenceId,
                    'message' => "تراکنش با موفقیت انجام شد.",
                    'status' => Transaction::COMPLETED,
                ]);

                $this->processHandling($transaction);

            }

            if ($payment->alreadyVerified()) {

                return response()->json([
                    'status' => 0,
                    'messsage' => $payment->message(),
                    'reference' => $payment->referenceId(),
                ]);

            }

            if ($payment->failed()) {
                $transaction->update([
                    'message' => __("site.not_paid"),
                    'status' => Transaction::FAILED,
                    'description' => $payment->message(),
                ]);

            }
        }

        return redirect()->to('/payment/result/' . $request->transactionId());

    }

    /**
     * Display a listing of the resource.
     */
    private function processHandling(Transaction $transaction) :void
    {
        match ($transaction->model_type) {
            Transaction::WALLET => app(WalletRepository::class)->completeTopUp($transaction->model_id),
            Transaction::PLAN => app(SubscribeRepository::class)->createSubscription($transaction->model_id, User::find($transaction->user_id)),
            Transaction::IDENTITY => app(IdentityRecordRepository::class)->changeStatusToPaid($transaction->model_id),
        };
    }

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
