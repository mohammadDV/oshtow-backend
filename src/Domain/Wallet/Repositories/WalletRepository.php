<?php

namespace Src\Domain\Wallet\Repositories;

use Src\Domain\Wallet\Models\Wallet;

class WalletRepository implements IWalletRepository
{
    public function findByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }

    public function create(array $data): Wallet
    {
        return Wallet::create($data);
    }

    public function update(Wallet $wallet, array $data): bool
    {
        return $wallet->update($data);
    }

    public function incrementBalance(Wallet $wallet, float $amount): bool
    {
        return $wallet->increment('balance', $amount);
    }

    public function decrementBalance(Wallet $wallet, float $amount): bool
    {
        return $wallet->decrement('balance', $amount);
    }

    public function getAvailableBalance(Wallet $wallet): float
    {
        return $wallet->available_balance;
    }
}