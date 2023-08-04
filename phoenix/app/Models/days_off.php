<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class days_off extends Model
{
    use HasFactory;

    protected $table = "days_offs";

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

    public function landmark(){
        return $this->hasMany('App\Models\Landmark','days_off_id','id');
    }
}
