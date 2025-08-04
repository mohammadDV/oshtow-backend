<?php

namespace Domain\IdentityRecord\Models;

use Domain\User\Models\User;
use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
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
     * Get the user that owns the identity record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the country that owns the identity record.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the province that owns the identity record.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the city that owns the identity record.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}