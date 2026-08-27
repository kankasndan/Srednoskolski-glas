<?php

namespace App\Models;

use Database\Factories\FeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    /** @use HasFactory<FeedbackFactory> */
    use HasFactory;

    protected $table = 'feedback';

    protected $fillable = [
        'user_id',
        'rating',
        'message',
        'reviewed_at',
        'reviewed_by',
        'staff_note',
    ];

    protected $casts = [
        'rating' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isReviewed(): bool
    {
        return $this->reviewed_at !== null;
    }

    public static function unreviewedCount(): int
    {
        return static::query()->whereNull('reviewed_at')->count();
    }
}
