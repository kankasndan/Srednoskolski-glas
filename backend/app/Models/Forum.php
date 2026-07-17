<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Forum extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'type', 'school_id', 'imageUrl', 'bannerUrl', 'threads_count', 'members_count'];

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function forumUser()
    {
        return $this->belongsToMany(User::class, 'forum_user');
    }
}
