<?php

namespace Src\Domain\Payment\Models;

use Src\Domain\Transaction\Models\Transaction;
use Src\Domain\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentHold extends Model
{
    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'expires_at',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'expires_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function release(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'released';
        return $this->save();
    }

    public function cancel(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'cancelled';
        return $this->save();
    }
}