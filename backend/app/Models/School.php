<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ["name", "city_id"];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function studetnData()
    {
        return $this->hasMany(StudentData::class, "student_data");
    }
}
