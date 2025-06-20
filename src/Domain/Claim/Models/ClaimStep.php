<?php

namespace Domain\Claim\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimStep extends Model
{
    /** @use HasFactory<\Database\Factories\ClaimFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'data',
        'step_id',
        'claim_id',
    ];

    /**
     * Get the project that owns the claim.
     */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
}