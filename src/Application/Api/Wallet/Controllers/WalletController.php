<?php

namespace Application\Api\Wallet\Controllers;

use App\Domain\Transaction\Repositories\ITransactionRepository;
use App\Domain\Wallet\Repositories\IWalletRepository;
use App\Domain\Payment\Repositories\IPaymentRepository;

use App\Models\User;
use Core\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function __construct(
        private readonly IWalletRepository $walletRepository,
        private readonly ITransactionRepository $transactionRepository,
        private readonly IPaymentRepository $paymentRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(): JsonResponse
    {
        $wallet = $this->walletRepository->findByUserId(auth()->id());

        return response()->json([
            'status' => 1,
            'data' => [
                'balance' => $wallet->balance,
                'available_balance' => $this->walletRepository->getAvailableBalance($wallet),
                'currency' => $wallet->currency,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function topUp(TopUpRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $wallet = $this->walletRepository->findByUserId(auth()->id());

            // Create transaction record
            $transaction = $this->transactionRepository->create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => 'pending',
                'reference' => $this->transactionRepository->generateReference(),
                'description' => 'Wallet top-up',
            ]);

            // Create bank transaction record
            $bankTransaction = $this->paymentRepository->createBankTransaction([
                'transaction_id' => $transaction->id,
                'bank_reference' => 'BANK-' . uniqid(),
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'payment_details' => $request->payment_details,
            ]);

            // TODO: Integrate with actual bank gateway
            // For now, we'll simulate a successful payment
            $bankTransaction->markAsProcessed();

            // Update wallet balance
            $this->walletRepository->incrementBalance($wallet, $request->amount);

            // Update transaction status
            $this->transactionRepository->update($transaction, ['status' => 'completed']);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Top-up successful',
                'data' => [
                    'transaction_reference' => $transaction->reference,
                    'new_balance' => $wallet->balance,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet top-up failed: ' . $e->getMessage());

            return response()->json([
                'status' => 0,
                'message' => 'Top-up failed. Please try again.',
            ], 500);
        }
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $senderWallet = $this->walletRepository->findByUserId(auth()->id());
            $recipient = User::where('email', $request->recipient_email)->first();
            $recipientWallet = $this->walletRepository->findByUserId($recipient->id);

            if (!$senderWallet->canWithdraw($request->amount)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Insufficient funds',
                ], 422);
            }

            // Create sender's transaction
            $senderTransaction = $this->transactionRepository->create([
                'wallet_id' => $senderWallet->id,
                'type' => 'transfer',
                'amount' => -$request->amount,
                'currency' => $senderWallet->currency,
                'status' => 'pending',
                'reference' => $this->transactionRepository->generateReference(),
                'description' => $request->description ?? 'Transfer to ' . $recipient->email,
                'recipient_wallet_id' => $recipientWallet->id,
            ]);

            // Create recipient's transaction
            $recipientTransaction = $this->transactionRepository->create([
                'wallet_id' => $recipientWallet->id,
                'type' => 'transfer',
                'amount' => $request->amount,
                'currency' => $recipientWallet->currency,
                'status' => 'pending',
                'reference' => $this->transactionRepository->generateReference(),
                'description' => 'Transfer from ' . auth()->user()->email,
                'related_transaction_id' => $senderTransaction->id,
            ]);

            // Update balances
            $this->walletRepository->decrementBalance($senderWallet, $request->amount);
            $this->walletRepository->incrementBalance($recipientWallet, $request->amount);

            // Update transaction statuses
            $this->transactionRepository->update($senderTransaction, ['status' => 'completed']);
            $this->transactionRepository->update($recipientTransaction, ['status' => 'completed']);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Transfer successful',
                'data' => [
                    'transaction_reference' => $senderTransaction->reference,
                    'new_balance' => $senderWallet->balance,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet transfer failed: ' . $e->getMessage());

            return response()->json([
                'status' => 0,
                'message' => 'Transfer failed. Please try again.',
            ], 500);
        }
    }
}