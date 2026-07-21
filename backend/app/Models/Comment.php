<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ['thread_id', 'parent_id', 'user_id', 'content'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
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

    public function mentions()
    {
        return $this->morphMany(Mention::class, 'mentionable');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
