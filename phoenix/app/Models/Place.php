<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Place extends Model
{
    use HasFactory;

    protected $table = "places";

    protected $fillable = [
        "ar_name",
        "en_name",
        "city_id",
        "open_time",
        "close_time",
        "category_id",
        "ar_description",
        "en_description",
        "ar_location",
        "en_location",
        "map_x",
        "map_y",
        "stars",
        "avg_rate",
        "phone_number",
        "breakfast",
        "lunch_dinner"
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

    public function setOpenTimeAttribute($value)
    {
        $this->attributes['open_time'] = Carbon::parse($value)->format('H:i');
    }
    public function getOpenTimeAttribute()
    {
         return Carbon::parse($this->attributes['open_time'])->format('H:i');
    }

    public function setCloseTimeAttribute($value)
    {
        $this->attributes['close_time'] = Carbon::parse($value)->format('H:i');
    }
    public function getCloseTimeAttribute()
    {
         return Carbon::parse($this->attributes['close_time'])->format('H:i');
    }

    public function city(): BelongsTo
    {
    return $this->belongsTo(city::class);
    }

    public function images(): HasMany
    {
    return $this->hasMany('App\Model\Image1');
    }

    public function plan1(){
        return $this->hasMany('App\Models\Plan','breakfast_id','id');
    }
    public function plan2(){
        return $this->hasMany('App\Models\Plan','lunch_id','id');
    }
    public function plan3(){
        return $this->hasMany('App\Models\Plan','dinner_id','id');
    }
    public function plan4(){
        return $this->hasMany('App\Models\Plan','hotel_id','id');
    }
    public function favorite()
    {
        return $this->belongsTo(Place::class);
    }
    public function category()
    {
    return $this->belongsTo(places_category::class);
    }
}
