<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mention extends Model
{
    protected $fillable = [
        'mentionable_id', 'mentionable_type', 'mentioning_user_id', 'mentioned_user_id',
    ];

    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function mentioningUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioning_user_id');
    }

    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }
}
