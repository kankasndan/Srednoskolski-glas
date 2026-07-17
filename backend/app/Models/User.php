<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'username',
    'email',
    'password',
    'provider',
    'provider_id',
    'imageUrl',
    'onboarding_completed_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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

    public function forumUser(): HasMany
    {
        return $this->hasMany(Forum::class);
    }

    public function studentData(): HasOne
    {
        return $this->hasOne(StudentData::class);
    }

    public function forums(): BelongsToMany
    {
        return $this->belongsToMany(Forum::class)->withTimestamps();
    }
}
