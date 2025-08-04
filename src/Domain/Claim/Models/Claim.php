<?php

namespace Domain\Claim\Models;

use Domain\Project\Models\Project;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends Model
{
    /** @use HasFactory<\Database\Factories\ClaimFactory> */
    use HasFactory;

    const PENDING = 'pending';
    const APPROVED = 'approved';
    const PAID = 'paid';
    const INPROGRESS = 'in_progress';
    const DELIVERED = 'delivered';
    const CANCELED = 'canceled';
    const ME = 'me';
    const OTHER = 'other';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'delivery_code',
        'confirmed_code',
    ];

    /**
     * Get the project that owns the claim.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that owns the claim.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that owns the claim.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    /**
     * Set the image attribute with domain replacement
     *
     * @param string $value
     * @return void
     */
    public function setImageAttribute($value)
    {
        if ($value && is_string($value)) {
            // Replace the old domain with the new one from config
            $this->attributes['image'] = str_replace(
                config('image.url-upload-file'),
                '',
                trim($value)
            );
        } else {
            $this->attributes['image'] = $value;
        }
    }

    /**
     * Get the image attribute with proper domain
     *
     * @param string $value
     * @return string|null
     */
    public function getImageAttribute($value)
    {
        if ($value && is_string($value)) {
            // Check if the value already has http:// or https:// protocol
            if (!preg_match('/^https?:\/\//', $value)) {
                return config('image.url-upload-file') . ltrim($value, '/');
            }
        }
        return $value;
    }

    /**
     * Set the image attribute with domain replacement
     *
     * @param string $value
     * @return void
     */
    public function setConfirmationImageAttribute($value)
    {
        if ($value && is_string($value)) {
            // Replace the old domain with the new one from config
            $this->attributes['confirmation_image'] = str_replace(
                config('image.url-upload-file'),
                '',
                trim($value)
            );
        } else {
            $this->attributes['confirmation_image'] = $value;
        }
    }

    /**
     * Get the image attribute with proper domain
     *
     * @param string $value
     * @return string|null
     */
    public function getConfirmationImageAttribute($value)
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