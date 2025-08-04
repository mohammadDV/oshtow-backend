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

    /**
     * Set the image attribute with domain replacement
     *
     * @param string $value
     * @return void
     */
    public function setDataAttribute($value)
    {
        if ($value && is_string($value)) {
            // Replace the old domain with the new one from config
            $this->attributes['data'] = str_replace(
                config('image.url-upload-file'),
                '',
                trim($value)
            );
        } else {
            $this->attributes['data'] = $value;
        }
    }

    /**
     * Get the image attribute with proper domain
     *
     * @param string $value
     * @return string|null
     */
    public function getDataAttribute($value)
    {
        if ($value && is_string($value)) {
            // Check if the value already has http:// or https:// protocol
            if (!preg_match('/^https?:\/\//', $value)) {
                return config('image.url-upload-file') . ltrim($value, '/');
            }
        }
        return $value;
    }
}