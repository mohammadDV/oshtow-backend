<?php

namespace Domain\Payment\Models;

use Domain\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    const PENDING = 'pending';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';
    const FAILED = 'failed';
    const WALLET = 'wallet';
    const PLAN = 'plan';
    const IDENTITY = 'identity';


    protected $fillable = [
        'model_id',
        'model_type',
        'user_id',
        'amount',
        'status',
        'reference',
        'bank_transaction_id',
        'description',
        'message',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}