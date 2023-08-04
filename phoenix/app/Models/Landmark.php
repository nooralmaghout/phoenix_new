<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Landmark extends Model
{
    use HasFactory;

    protected $table = "landmarks";

    protected $fillable = [
        "ar_name",
        "en_name",
        "city_id",
        "open_time",
        "close_time",
        "type_id",
        "catgory_id",
        "ar_description",
        "en_description",
        "ar_location",
        "en_location",
        "map_x",
        "map_y",
        "days_off_id"
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

    public function plan(){
        return $this->hasMany('App\Models\Plan','landmark_id','id');
    }
    public function days_off(): BelongsTo
    {
    return $this->belongsTo(days_off::class);
    }
    public function category(): BelongsTo
    {
    return $this->belongsTo(category::class);
    }
    public function type(): BelongsTo
    {
    return $this->belongsTo(landmarks_type::class);
    }
}
