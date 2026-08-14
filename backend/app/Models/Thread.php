<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Thread extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'description', 'upvotes', 'views', 'user_id', 'forum_id', 'is_anonymous', 'deleted_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Thread $thread): void {
            if ($thread->isForceDeleting()) {
                return;
            }

            $thread->deleted_by = Auth::id();
            $thread->saveQuietly();
        });

        static::deleted(function (Thread $thread): void {
            $thread->comments()->delete();
            $thread->threadAttachment()->delete();
        });
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function threadAttachment(): HasMany
    {
        return $this->hasMany(ThreadAttachment::class)->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Threads that publicly identify their author. Anonymous posts stay in
     * the feed with a hidden author, but must not appear on /u/{username}.
     */
    public function scopePubliclyAttributed(Builder $query): void
    {
        $query->where('is_anonymous', false);
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function mentions(): MorphMany
    {
        return $this->morphMany(Mention::class, 'mentionable');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function viewsByUsers(): HasMany
    {
        return $this->hasMany(ThreadView::class);
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'thread_follows')->withTimestamps();
    }

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }
}
