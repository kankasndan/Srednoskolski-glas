<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentData extends Model
{
    protected $fillable = ['user_id', "school_id", "vocation_id", "grade"];

    public function school()
    {
        return $this->hasMany(School::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function vocation()
    {
        return $this->hasMany(Vocation::class);
    }
}
