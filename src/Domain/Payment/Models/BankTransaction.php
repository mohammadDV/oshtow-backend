<?php

namespace Domain\Payment\Models;

use Domain\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'bank_reference',
        'payment_method',
        'status',
        'payment_details',
        'bank_response',
        'processed_at',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'bank_response' => 'array',
        'processed_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function markAsProcessed(): bool
    {
        $this->status = 'completed';
        $this->processed_at = now();
        return $this->save();
    }

    public function markAsFailed(array $response = []): bool
    {
        $this->status = 'failed';
        $this->bank_response = $response;
        return $this->save();
    }
}
