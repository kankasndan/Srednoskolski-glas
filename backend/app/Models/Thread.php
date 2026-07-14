<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = ["title", "description", "upvotes", "views", "user_id", "forum_id", "is_anonymous"];

    public function threadAttachment()
    {
        return $this->hasMany(ThreadAttachment::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }
}
