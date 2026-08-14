<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'username',
    'email',
    'password',
    'imageUrl',
    'onboarding_completed_at',
    'last_active_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Give users without a picture one of the default avatars at random, so
        // every account always has an image to display.
        static::creating(function (User $user): void {
            if (empty($user->imageUrl)) {
                $defaults = config('avatars.defaults', []);

                if (! empty($defaults)) {
                    $user->imageUrl = $defaults[array_rand($defaults)];
                }
            }
        });
    }

    public function studentData(): HasOne
    {
        return $this->hasOne(StudentData::class);
    }

    public function forums(): BelongsToMany
    {
        return $this->belongsToMany(Forum::class)->withTimestamps();
    }

    /** Users who follow this user. */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'user_follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /** Users this user follows. */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'user_follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function threadViews()
    {
        return $this->hasMany(ThreadView::class);
    }

    public function feedHides()
    {
        return $this->hasMany(FeedHide::class);
    }

    public function viewedThreads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class, 'thread_views')
            ->withPivot('last_viewed_at')
            ->withTimestamps();
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    /** Threads this user follows (spec 6.6 — visual only in MVP). */
    public function followedThreads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class, 'thread_follows')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function sanctions()
    {
        return $this->hasMany(Sanction::class, 'user_id');
    }

    /**
     * True when the user has an active non-warning sanction (temp or permanent ban).
     */
    public function isBanned(): bool
    {
        return $this->sanctions()
            ->where('type', '!=', 'warning')
            ->whereNull('revoked_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function appeals()
    {
        return $this->hasMany(Appeal::class);
    }

    public function forum()
    {
        return $this->hasOne(Forum::class);
    }

    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'feed_topics');
    }

    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    /**
     * True when the user has student school data (required to start threads).
     */
    public function belongsToSchool(): bool
    {
        $this->loadMissing('studentData');

        return $this->studentData?->school_id !== null;
    }

    /**
     * Forum id for the user's school, if any.
     */
    public function schoolForumId(): ?int
    {
        $this->loadMissing('studentData.school.forum');

        $forumId = $this->studentData?->school?->forum?->id;

        return $forumId !== null ? (int) $forumId : null;
    }

    /**
     * Whether the user may create a thread in this forum (Spatie + school rules).
     */
    public function canCreateThreadIn(Forum $forum): bool
    {
        return $this->can('create', [Thread::class, $forum]);
    }

    public function canCreateComments(): bool
    {
        return $this->hasCompletedOnboarding()
            && ($this->can('create comments') || $this->can('manage comments'));
    }
}
