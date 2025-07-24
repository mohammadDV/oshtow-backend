<?php

namespace Domain\Project\Models;

use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Claim\Models\Claim;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    const PASSENGER = "passenger";
    const SENDER = "sender";
    const PENDING = "pending";
    const APPROVED = "approved";
    const INPROGRESS = "in_progress";
    const COMPLETED = "completed";
    const CANCELLED = "canceled";
    const REJECT = "reject";
    const FAILED = "failed";

    protected $fillable = [
        'title',
        'type',
        'path_type',
        'amount',
        'weight',
        'dimensions',
        'active',
        'status',
        'vip',
        'priority',
        'send_date',
        'receive_date',
        'o_country_id',
        'o_province_id',
        'o_city_id',
        'd_country_id',
        'd_province_id',
        'd_city_id',
        'address',
        'description',
        'user_id',
    ];

    protected $casts = [
        'send_date' => 'date',
        'receive_date' => 'date',
        'amount' => 'integer',
        'weight' => 'integer',
        'active' => 'integer',
        'vip' => 'boolean',
        'priority' => 'integer',
    ];

    public function categories()
    {
        return $this->belongsToMany(ProjectCategory::class, 'category_project', 'project_id', 'category_id');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function claimSelected()
    {
        return $this->hasMany(Claim::class)->where('status', '!=', Claim::PENDING);
    }

    public function claimsLimit()
    {
        return $this->hasMany(Claim::class)->limit(3);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function oCountry()
    {
        return $this->belongsTo(Country::class, 'o_country_id');
    }

    public function oProvince()
    {
        return $this->belongsTo(Province::class, 'o_province_id');
    }

    public function oCity()
    {
        return $this->belongsTo(City::class, 'o_city_id');
    }

    public function dCountry()
    {
        return $this->belongsTo(Country::class, 'd_country_id');
    }

    public function dProvince()
    {
        return $this->belongsTo(Province::class, 'd_province_id');
    }

    public function dCity()
    {
        return $this->belongsTo(City::class, 'd_city_id');
    }
}