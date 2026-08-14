<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ['content'];

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

    /**
     * Live comments, plus tombstones that still have a live direct reply.
     */
    public function scopeVisibleInThread(Builder $query): void
    {
        $query->where(function (Builder $inner): void {
            $inner->whereNull('deleted_at')
                ->orWhereHas('replies', fn (Builder $replies) => $replies->withoutTrashed());
        });
    }

    /**
     * Drop comments on the profile owner's own anonymous threads so the
     * public comments tab cannot be used to deanonymize those posts.
     */
    public function scopeWithoutOwnAnonymousThreads(Builder $query, User $profileUser): void
    {
        $query->whereDoesntHave('thread', function (Builder $thread) use ($profileUser): void {
            $thread->where('user_id', $profileUser->id)
                ->where('is_anonymous', true);
        });
    }

    /**
     * How many direct replies would appear if this comment is expanded.
     */
    public function scopeWithVisibleRepliesCount(Builder $query): void
    {
        $query->withCount([
            'replies as replies_count' => fn (Builder $replies) => $replies->visibleInThread(),
        ]);
    }

    /**
     * @return list<string>
     */
    public static function authorWith(): array
    {
        return [
            'user.studentData.school.city',
            'user.studentData.school.forum',
            'thread',
        ];
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
