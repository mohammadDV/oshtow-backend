<?php

namespace Domain\Chat\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chat extends Model
{
    /** @use HasFactory<\Database\Factories\ChatFactory> */
    use HasFactory;

    CONST STATUS_ACTIVE = 'active';
    CONST STATUS_CLOSED = 'closed';

    protected $guarded = [];

    public function messages() {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Get the last message of the chat
     *
     * @return HasOne
     */
    public function lastMessage(): HasOne {
        return $this->hasOne(ChatMessage::class)->latest();
    }

     /**
     * Get the user that owns the Chat
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

     /**
     * Get the user that target the Chat
     *
     * @return BelongsTo
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}