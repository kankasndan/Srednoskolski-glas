<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = ["name", "description"];

    public function users()
    {
        return $this->belongsToMany(User::class, "feed_topics");
    }
}
