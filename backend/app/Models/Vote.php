<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Vote extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'votable_type',
        'votable_id',
    ];

    /**
     * Record an upvote and bump the denormalized counter.
     *
     * @param  Thread|Comment  $votable
     */
    public static function addFor(User $user, Model $votable): void
    {
        static::query()->create([
            'user_id' => $user->id,
            'votable_type' => $votable->getMorphClass(),
            'votable_id' => $votable->getKey(),
        ]);

        $votable->increment('upvotes');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votable(): MorphTo
    {
        return $this->morphTo();
    }
}
