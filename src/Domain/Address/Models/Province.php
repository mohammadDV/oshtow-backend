<?php

namespace Domain\Address\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    /** @use HasFactory<\Database\Factories\ProvinceFactory> */
    use HasFactory;
    protected $guarded = [];


    public function cities() {
        return $this->hasMany(City::class);
    }

    public function country() {
        return $this->belongsTo(Country::class);
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