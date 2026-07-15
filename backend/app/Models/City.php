<?php

namespace App\Models;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ["name"];

    public function school()
    {
        return $this->hasMany(School::class);
    }
}
