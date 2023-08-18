<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Plan extends Model
{
    use HasFactory;

    protected $table = "plans";

    protected $fillable = [ 
    "name",
    "tourist_id",
    "breakfast_id",
    "lunch_id",
    "dinner_id",
    "landmark_id",
    "hotel_id",
    "event_id",
    "start_time",
    "end_time",
    "type_id",
    "city_id",
    "start_date",
    "stars",
    ];

    public $timestamps = false;

    public function setArNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }
    public function setEnNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    public function city()
    {
    return $this->belongsTo('App\Models\city');
    }

    public function type()
    {
    return $this->belongsTo('App\Models\plan_type');
    }
    
    public function landmark()
    {
    return $this->belongsTo('App\Models\Landmark');
    }

    public function event()
    {
    return $this->belongsTo('App\Models\Event');
    }

    public function breakfast()
    {
    return $this->belongsTo('App\Models\Place');
    }

    public function lunch()
    {
    return $this->belongsTo('App\Models\Place');
    }

    public function dinner()
    {
    return $this->belongsTo('App\Models\Place');
    }
    public function hotel()
    {
    return $this->belongsTo('App\Models\Place');
    }
}
