<?php

namespace Domain\Transaction\Repositories;

use Domain\Transaction\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;

interface ITransactionRepository
{
    public function create(array $data): Transaction;
    public function update(Transaction $transaction, array $data): bool;
    public function findByReference(string $reference): ?Transaction;
    public function findByWalletId(int $walletId, array $filters = []): LengthAwarePaginator;
    public function generateReference(): string;
}
