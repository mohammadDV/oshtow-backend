<?php

namespace Src\Domain\Transaction\Models;

use Src\Domain\Payment\Models\BankTransaction;
use Src\Domain\Payment\Models\PaymentHold;
use Src\Domain\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'currency',
        'status',
        'reference',
        'description',
        'metadata',
        'related_transaction_id',
        'recipient_wallet_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function bankTransaction(): HasOne
    {
        return $this->hasOne(BankTransaction::class);
    }

    public function paymentHold(): HasOne
    {
        return $this->hasOne(PaymentHold::class);
    }

    public static function generateReference(): string
    {
        return 'TRX-' . strtoupper(uniqid());
    }
}