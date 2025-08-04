<?php

namespace Domain\Chat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    /** @use HasFactory<\Database\Factories\ChatMessageFactory> */
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_READ = 'read';

    protected $guarded      = [];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Set the image attribute with domain replacement
     *
     * @param string $value
     * @return void
     */
    public function setFileAttribute($value)
    {
        if ($value && is_string($value)) {
            // Replace the old domain with the new one from config
            $this->attributes['file'] = str_replace(
                config('image.url-upload-file'),
                '',
                trim($value)
            );
        } else {
            $this->attributes['file'] = $value;
        }
    }

    /**
     * Get the image attribute with proper domain
     *
     * @param string $value
     * @return string|null
     */
    public function getFileAttribute($value)
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