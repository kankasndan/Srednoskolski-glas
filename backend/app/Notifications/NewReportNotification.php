<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Report;
use App\Models\Thread;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReportNotification extends Notification
{
    use Queueable;

    private const REASON_LABELS = [
        'spam' => 'Спам',
        'insulting_content' => 'Навредлива содржина',
        'misinformation' => 'Дезинформација',
        'age_inappropriate' => 'Несоодветна содржина',
        'other' => 'Друго',
    ];

    public function __construct(public Report $report) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Payload shown in the admin header bell dropdown.
     *
     * @return array{title: string, message: string, url: string, report_id: int}
     */
    public function toArray(object $notifiable): array
    {
        $target = match ($this->report->reportable_type) {
            Thread::class => 'дискусија',
            Comment::class => 'коментар',
            default => 'содржина',
        };

        $reason = self::REASON_LABELS[$this->report->reason] ?? $this->report->reason;

        return [
            'title' => 'Нова пријава',
            'message' => "Пријавен е {$target} — {$reason}.",
            'url' => route('report.index'),
            'report_id' => $this->report->id,
        ];
    }
}
