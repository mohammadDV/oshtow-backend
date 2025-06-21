<?php

namespace Domain\Plan\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory;

    protected $guarded = [];

    public function plan() {
        return $this->belongsTo(Plan::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}