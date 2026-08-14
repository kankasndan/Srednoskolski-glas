<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentData extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'vocation_id',
        'grade',
        'school_changed_at',
        'grade_promoted_at',
    ];

    protected $hidden = [
        'school_changed_at',
        'grade_promoted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grade' => 'integer',
            'school_changed_at' => 'datetime',
            'grade_promoted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function vocation(): BelongsTo
    {
        return $this->belongsTo(Vocation::class);
    }
}
