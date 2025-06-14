<?php

namespace Src\Domain\Payment\Repositories;

use Src\Domain\Payment\Models\BankTransaction;
use Src\Domain\Payment\Models\PaymentHold;
use Illuminate\Support\Collection;

class PaymentRepository implements IPaymentRepository
{
    public function createBankTransaction(array $data): BankTransaction
    {
        return BankTransaction::create($data);
    }

    public function createPaymentHold(array $data): PaymentHold
    {
        return PaymentHold::create($data);
    }

    public function updateBankTransaction(BankTransaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    public function updatePaymentHold(PaymentHold $hold, array $data): bool
    {
        return $hold->update($data);
    }

    public function releasePaymentHold(PaymentHold $hold): bool
    {
        return $hold->release();
    }

    public function cancelPaymentHold(PaymentHold $hold): bool
    {
        return $hold->cancel();
    }

    public function getPendingHoldsByWalletId(int $walletId): Collection
    {
        return PaymentHold::where('wallet_id', $walletId)
            ->where('status', 'pending')
            ->get();
    }
}