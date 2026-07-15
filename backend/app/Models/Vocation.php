<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vocation extends Model
{
    protected $fillable = ['name'];

    public function studentData(): HasMany
    {
        return $this->hasMany(StudentData::class);
    }
}
