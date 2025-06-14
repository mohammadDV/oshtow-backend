<?php

namespace Src\Domain\Wallet\Models;

use Src\Domain\Payment\Models\PaymentHold;
use Src\Domain\Transaction\Models\Transaction;
use Src\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function paymentHolds(): HasMany
    {
        return $this->hasMany(PaymentHold::class);
    }

    public function getAvailableBalanceAttribute(): float
    {
        $heldAmount = $this->paymentHolds()
            ->where('status', 'pending')
            ->sum('amount');

        return $this->balance - $heldAmount;
    }

    public function canWithdraw(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }
}