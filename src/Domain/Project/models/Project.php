<?php

namespace Domain\Project\models;

use Domain\Address\models\City;
use Domain\Address\models\Country;
use Domain\Address\models\Province;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $guarded = [];

    public function categories()
    {
        return $this->belongsToMany(ProjectCategory::class, 'category_project', 'project_id', 'category_id');
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
