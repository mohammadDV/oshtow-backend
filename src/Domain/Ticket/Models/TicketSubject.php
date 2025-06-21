<?php

namespace Domain\Ticket\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSubject extends Model
{
    /** @use HasFactory<\Database\Factories\TicketSubjectFactory> */
    use HasFactory;
    protected $guarded = [];
}
