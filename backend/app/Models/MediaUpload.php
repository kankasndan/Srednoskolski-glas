<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaUpload extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'file_id',
        'path',
        'url',
        'directory',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
