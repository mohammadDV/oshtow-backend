<?php

namespace Domain\Post\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory, Sluggable;

    protected $guarded = [];

    public function sluggable() : array
    {
        return [
          'slug' => [
              'source' => 'title'
          ]
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeNameAttribute()
    {
        return __('site.' . Config('custom.POST_TYPE')[$this->type]);
    }

    public function getStatusNameAttribute()
    {
        return $this->status == 1 ? __('site.Active') : __('site.Inactive');
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
    public function setVideoAttribute($value)
    {
        if ($value && is_string($value)) {
            // Replace the old domain with the new one from config
            $this->attributes['video'] = str_replace(
                config('image.url-upload-file'),
                '',
                trim($value)
            );
        } else {
            $this->attributes['video'] = $value;
        }
    }

    /**
     * Get the image attribute with proper domain
     *
     * @param string $value
     * @return string|null
     */
    public function getVideoAttribute($value)
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