<?php

namespace Application\Api\Payment\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Claim\Models\Claim;
use Domain\Claim\Repositories\ClaimRepository;
use Domain\Claim\Repositories\Contracts\IClaimRepository;
use Domain\IdentityRecord\Repositories\IdentityRecordRepository;
use Domain\Payment\Models\Transaction;
use Domain\Payment\Repositories\Contracts\IPaymentRepository;
use Domain\Plan\Repositories\SubscribeRepository;
use Domain\User\Models\User;
use Domain\User\Services\TelegramNotificationService;
use Domain\Wallet\Repositories\WalletRepository;
use Evryn\LaravelToman\CallbackRequest;
use Evryn\LaravelToman\Facades\Toman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class PaymentController extends Controller
{
    use GlobalFunc;

    public function __construct(
        protected IPaymentRepository $repository,
        protected IClaimRepository $claimRepository,
        protected TelegramNotificationService $service
    ) {}


    /**
     * Get the transaction pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :JsonResponse
    {
        return response()->json($this->repository->index($request));
    }

    /**
     * Display a listing of the resource.
     */
    public function payment(Request $request)
    {
        $code = Transaction::generateHash($request->input('transaction'));

        if ($code != $request->input('sign')) {
            return [
                'status' => 0,
                'message' => __("site.invalid_request")
            ];
        }

        $transaction = Transaction::findOrfail($request->input('transaction'));

        $user = User::findOrfail($transaction->user_id);

        $tomanRequest = Toman::amount($transaction->amount)
            ->description('Subscribe the first plan')
            ->callback(route('user.payment.callback'))
            ->mobile($user->mobile)
            ->email($user->email)
            ->request();

        if ($tomanRequest->successful()) {

            $transaction->update([
                'bank_transaction_id' => $tomanRequest->transactionId()
            ]);

            return $tomanRequest->pay(); // Redirect to payment URL
        }

        return Redirect::to('http://localhost:3000/payment/result/' . $request->transactionId());

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
                    'message' => __("site.transaction_successful"),
                    'status' => Transaction::COMPLETED,
                ]);

                $this->processHandling($transaction);

                $this->service->sendNotification(
                    config('telegram.chat_id'),
                    'پرداخت موفق درگاه' . PHP_EOL .
                    'id ' . $transaction->user_id . PHP_EOL .
                    'transaction' . $transaction->id . PHP_EOL .
                    'type' . $transaction->model_type . PHP_EOL .
                    'amount' . number_format($transaction->amount) . PHP_EOL .
                    'time ' . now()
                );

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

        return Redirect::to('/payment/result/' . $request->transactionId());
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
            Transaction::SECURE => app(ClaimRepository::class)->createPaymentSecure(Claim::find($transaction->model_id)),
        };
    }

}