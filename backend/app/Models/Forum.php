<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    protected $fillable = ["name", "description", "type", "imageUrl", "bannerUrl"];

    public function thread()
    {
        return $this->hasMany(Thread::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'forum_user');
    }
}
