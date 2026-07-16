<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'reporter_id', 'reportable_id', 'reportable_type', 'reason', 'other_reason',
        'status', 'source', 'ai_confidence', 'ai_reasoning', 'reviewed_by',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportable()
    {
        return $this->morphTo();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }
}