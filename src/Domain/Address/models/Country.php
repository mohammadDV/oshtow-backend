<?php

namespace Domain\Address\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /** @use HasFactory<\Database\Factories\CountryFactory> */
    use HasFactory;

    protected $guarded = [];


    public function provinces() {
        return $this->hasMany(Province::class);
    }
}
