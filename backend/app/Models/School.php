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

    public function userSchool()
    {
        return $this->hasMany(User::class, "student_data");
    }
}
