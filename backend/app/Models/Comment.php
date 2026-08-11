<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ['thread_id', 'parent_id', 'user_id', 'content', 'deleted_by'];

    protected static function booted(): void
    {
        static::deleting(function (Comment $comment): void {
            if ($comment->isForceDeleting()) {
                return;
            }

            $comment->deleted_by = Auth::id();
            $comment->saveQuietly();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->withTrashed();
    }

    public function allReplies(): HasMany
    {
        // Nested replies stay chronological so a conversation reads top-to-bottom.
        $relation = $this->replies()
            ->oldest()
            ->with(['user.studentData.school.city', 'user.studentData.school.forum', 'allReplies']);

        $userId = auth('web')->id() ?? auth()->id();

        if ($userId !== null) {
            $relation->withExists([
                'votes as has_voted' => fn ($query) => $query->where('user_id', $userId),
            ]);
        }

        return $relation;
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

    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable');
    }
}
