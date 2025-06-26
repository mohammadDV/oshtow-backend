<?php

namespace Domain\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Domain\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'password',
        'first_name',
        'last_name',
        'nickname',
        'mobile',
        'biography',
        'profile_photo_path',
        'bg_photo_path',
        'national_code',
        'point',
        'rate',
        'role_id',
        'level',
        'status',
        'email',
        'is_private',
        'is_report',
        'google_id',
        'customer_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'customer_number',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */


    protected $visible = [
        'id','first_name','last_name','nickname', 'clubs','biography','profile_photo_path','bg_photo_path','point','rate','role_id', 'is_private', 'is_report', 'email', 'status', 'created_at'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function getPermissionRoleNames()
    {
        $permissions = $this->permissions;

        if (method_exists($this, 'roles')) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values()->pluck('name');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user's wallets.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function getStatusNameAttribute()
    {
        return $this->status == 1 ? __('site.Active') : __('site.Inactive');
    }

    public static function generateCustumerNumber(): string
    {
        do {
            $number = random_int(1111111111, 9999999999);
            $exists = self::query()
                ->where('customer_number', $number)
                ->exists();
        } while ($exists);

        return (string)$number;
    }
}