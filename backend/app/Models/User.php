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
    'email_verified_at',
    'password',
    'imageUrl',
    'provider',
    'provider_id',
    'role',
    'onboarding_completed_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed_at' => 'datetime',
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

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }

    public function appeals()
    {
        return $this->hasMany(Appeal::class);
    }
}
