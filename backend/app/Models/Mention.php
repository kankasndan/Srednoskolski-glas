<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mention extends Model
{
    protected $fillable = [
        'mentionable_id', 'mentionable_type', 'mentioning_user_id', 'mentioned_user_id',
    ];

    public function mentionable()
    {
        return $this->morphTo();
    }

    public function mentioningUser()
    {
        return $this->belongsTo(User::class, 'mentioning_user_id');
    }

    public function mentionedUser()
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }
}