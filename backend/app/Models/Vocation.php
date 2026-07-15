<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vocation extends Model
{
    protected $fillable = ["name"];

    public function studentData()
    {
        return $this->hasMany(User::class, "student_data");
    }
}
