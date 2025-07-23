<?php

namespace Domain\Payment\Models;

use Domain\Claim\Models\Claim;
use Domain\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSecure extends Model
{

    const PENDING = 'pending';
    const RELEASED = 'released';
    const CANCELLED = 'cancelled';
    const WALLET = 'wallet';
    const BANK = 'bank';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $hidden = [
        'model_type',
        'model_id',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function release(): bool
    {
        if ($this->status !== PaymentSecure::PENDING) {
            return false;
        }

        $this->status = PaymentSecure::RELEASED;
        return $this->save();
    }

    public function cancel(): bool
    {
        if ($this->status !== PaymentSecure::PENDING) {
            return false;
        }

        $this->status = PaymentSecure::CANCELLED;
        return $this->save();
    }
}