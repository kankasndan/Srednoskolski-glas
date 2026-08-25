<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Database\Factories\SanctionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Sanction extends Model
{
    /** @use HasFactory<SanctionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'issued_by', 'type', 'reason', 'report_id',
        'expires_at', 'acknowledged_at', 'revoked_at', 'revoked_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Bans and timed restrictions — not warnings.
     */
    public function scopeBans(Builder $query): void
    {
        $query->where('type', '!=', 'warning');
    }

    /**
     * Not revoked and not past expires_at (permanent bans have a null expiry).
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('revoked_at')
            ->where(function (Builder $inner): void {
                $inner->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Revoked, soft-deleted, or past expires_at. Use with withTrashed().
     */
    public function scopeEnded(Builder $query): void
    {
        $query->where(function (Builder $outer): void {
            $outer->whereNotNull('revoked_at')
                ->orWhereNotNull('deleted_at')
                ->orWhere(function (Builder $inner): void {
                    $inner->whereNotNull('expires_at')
                        ->where('expires_at', '<=', now());
                });
        });
    }

    public function endedAt(): ?Carbon
    {
        if ($this->revoked_at !== null) {
            return $this->revoked_at;
        }

        if ($this->deleted_at !== null) {
            return $this->deleted_at;
        }

        if ($this->expires_at !== null && $this->expires_at->lte(now())) {
            return $this->expires_at;
        }

        return null;
    }

    /**
     * Popup type understood by the frontend SanctionDialog.
     */
    public function noticeType(): string
    {
        return match ($this->type) {
            'permanent_ban' => 'permanent_ban',
            'warning' => 'warning',
            'custom' => 'custom',
            default => '7-day',
        };
    }

    /**
     * Payload the client needs to render the sanction popup, reason, and appeal.
     *
     * @return array{
     *     id: int,
     *     type: string,
     *     expires_at: string|null,
     *     reason: string|null,
     *     content: array{type: string, title: ?string, body: ?string, gif_url: ?string}|null,
     *     can_appeal: bool,
     *     has_pending_appeal: bool
     * }
     */
    public function clientNoticePayload(?string $type = null): array
    {
        $this->loadMissing(['appeal', 'report.reportable']);

        $noticeType = $type ?? $this->noticeType();

        return [
            'id' => $this->id,
            'type' => $noticeType,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'reason' => $this->reason,
            'content' => $this->relatedContentPayload(),
            'can_appeal' => $noticeType !== 'ban_ended' && $this->isAppealable(),
            'has_pending_appeal' => $this->appeal?->status === 'pending',
        ];
    }

    public function isAppealable(): bool
    {
        if ($this->appeal()->exists()) {
            return false;
        }

        if ($this->revoked_at !== null || $this->trashed()) {
            return false;
        }

        if ($this->type !== 'warning' && $this->expires_at !== null && $this->expires_at->lte(now())) {
            return false;
        }

        return true;
    }

    /**
     * @return array{type: string, title: ?string, body: ?string, gif_url: ?string}|null
     */
    public function relatedContentPayload(): ?array
    {
        $this->loadMissing('report.reportable');
        $reportable = $this->report?->reportable;

        if ($reportable instanceof Comment) {
            $body = trim((string) $reportable->content);

            return [
                'type' => 'comment',
                'title' => null,
                'body' => $body !== '' ? $body : null,
                'gif_url' => $reportable->gif_url,
            ];
        }

        if ($reportable instanceof Thread) {
            $body = trim(HtmlSanitizer::plainText($reportable->description));

            return [
                'type' => 'thread',
                'title' => $reportable->title,
                'body' => $body !== '' ? $body : null,
                'gif_url' => null,
            ];
        }

        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function appeal(): HasOne
    {
        return $this->hasOne(Appeal::class);
    }
}
