<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reporter_id', 'reportable_id', 'reportable_type', 'reason', 'other_reason',
        'status', 'source', 'reviewed_by',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo()->constrain([
            Comment::class => fn (Builder $query) => $query->withTrashed(),
            Thread::class => fn (Builder $query) => $query->withTrashed(),
        ]);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }

    public static function pendingTargetCount(): int
    {
        return (int) static::query()
            ->where('status', 'pending')
            ->selectRaw('count(distinct concat(reportable_type, "-", reportable_id)) as aggregate')
            ->value('aggregate');
    }
}
