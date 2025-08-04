<?php

namespace Domain\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WithdrawalTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\WithdrawalTransactionFactory> */
    use HasFactory;

    const PENDING = "pending";
    const COMPLETED = "completed";
    const REJECT = "reject";

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public static function generateReference(): string
    {
        do {
            $reference = random_int(1111111111, 9999999999);
            $exists = self::query()
                ->where('reference', $reference)
                ->exists();
        } while ($exists);

        return (string)$reference;
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

}
