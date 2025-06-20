<?php

namespace Domain\Payment\Repositories;

use Domain\Payment\Models\BankTransaction;
use Domain\Payment\Models\PaymentHold;
use Illuminate\Support\Collection;

interface IPaymentRepository
{
    public function createBankTransaction(array $data): BankTransaction;
    public function createPaymentHold(array $data): PaymentHold;
    public function updateBankTransaction(BankTransaction $transaction, array $data): bool;
    public function updatePaymentHold(PaymentHold $hold, array $data): bool;
    public function releasePaymentHold(PaymentHold $hold): bool;
    public function cancelPaymentHold(PaymentHold $hold): bool;
    public function getPendingHoldsByWalletId(int $walletId): Collection;
}
