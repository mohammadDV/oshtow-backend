<?php

namespace Domain\Ticket\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $guarded = [];

    CONST STATUS_ACTIVE = 'active';
    CONST STATUS_CLOSED = 'closed';

    public function subject() {
        return $this->belongsTo(TicketSubject::class, 'subject_id', 'id');
    }

    public function messages() {
        return $this->hasMany(TicketMessage::class);
    }
}
