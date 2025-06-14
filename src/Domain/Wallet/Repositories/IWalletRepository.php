<?php

namespace Src\Domain\Wallet\Repositories;

use Src\Domain\Wallet\Models\Wallet;

interface IWalletRepository
{
    public function findByUserId(int $userId): ?Wallet;
    public function create(array $data): Wallet;
    public function update(Wallet $wallet, array $data): bool;
    public function incrementBalance(Wallet $wallet, float $amount): bool;
    public function decrementBalance(Wallet $wallet, float $amount): bool;
    public function getAvailableBalance(Wallet $wallet): float;
}