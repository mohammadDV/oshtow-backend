<?php

namespace Domain\IdentityRecord\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentityRecord extends Model
{
    /** @use HasFactory<\Database\Factories\IdentityRecordFactory> */
    use HasFactory;

    const PENDING = 'pending';
    const PAID = 'paid';
    const COMPLETED = 'completed';
    const REJECT = 'reject';

    protected $guarded = [];

    /**
     * Get the user that owns the claim.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}