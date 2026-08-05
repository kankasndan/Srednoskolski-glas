<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class City extends Model
{
    protected $fillable = ['name'];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function studentData(): HasManyThrough
    {
        return $this->hasManyThrough(StudentData::class, School::class);
    }
}
