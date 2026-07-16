<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = ["thread_id", "parent_id", "user_id", "content"];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function allReplies(): HasMany
    {
        return $this->replies()->with(['user.studentData.school.city', 'allReplies']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
