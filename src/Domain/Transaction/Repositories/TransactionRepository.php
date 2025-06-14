<?php

namespace Src\Domain\Transaction\Repositories;

use Src\Domain\Transaction\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository implements ITransactionRepository
{
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function update(Transaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    public function findByReference(string $reference): ?Transaction
    {
        return Transaction::where('reference', $reference)->first();
    }

    public function findByWalletId(int $walletId, array $filters = []): LengthAwarePaginator
    {
        $query = Transaction::where('wallet_id', $walletId)
            ->with(['bankTransaction', 'recipientWallet.user']);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function generateReference(): string
    {
        return 'TRX-' . strtoupper(uniqid());
    }
}