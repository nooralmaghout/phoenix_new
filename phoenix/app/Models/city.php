<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class city extends Model
{
    use HasFactory;

    protected $table = "citys";

    protected $fillable = [
        "ar_name",
        "en_name",
    ];

    public $timestamps = false;

    public function setArNameAttribute($value)
    {
        $this->attributes['ar_name'] = strtolower($value);
    }
    public function setEnNameAttribute($value)
    {
        $this->attributes['en_name'] = strtolower($value);
    }

    public function event(): HasMany
    {
        return $this->hasMany('App\Models\Event','city_id','id');
    }
    public function landmark(): HasMany
    {
        return $this->hasMany('App\Models\Landmark','city_id','id');
    }
    public function plan(){
        return $this->hasMany('App\Models\Plan','city_id','id');
    }
}
