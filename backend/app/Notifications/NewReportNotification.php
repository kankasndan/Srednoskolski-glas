<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Report;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Spatie\Permission\Models\Role;

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

    public function __construct(
        public Report $report,
        public int $count = 1,
    ) {}

    /**
     * One unread bell item per reportable. Extra reports bump the count.
     */
    public static function syncForReport(Report $report): void
    {
        $count = Report::query()
            ->where('reportable_type', $report->reportable_type)
            ->where('reportable_id', $report->reportable_id)
            ->where('status', 'pending')
            ->count();

        $roleNames = Role::query()
            ->whereIn('name', ['super_admin', 'admin', 'moderator'])
            ->pluck('name');

        if ($roleNames->isEmpty()) {
            return;
        }

        $staff = User::role($roleNames->all())->get();

        if ($staff->isEmpty()) {
            return;
        }

        $notification = new self($report, $count);
        $payload = $notification->toArray($staff->first());

        foreach ($staff as $user) {
            $existing = $user->notifications()
                ->where('type', self::class)
                ->where('data->reportable_type', $report->reportable_type)
                ->where('data->reportable_id', $report->reportable_id)
                ->whereNull('read_at')
                ->first();

            if ($existing !== null) {
                $existing->forceFill([
                    'data' => $payload,
                ])->save();

                continue;
            }

            $user->notify($notification);
        }
    }

    public static function markTargetRead(Report $report): void
    {
        $roleNames = Role::query()
            ->whereIn('name', ['super_admin', 'admin', 'moderator'])
            ->pluck('name');

        if ($roleNames->isEmpty()) {
            return;
        }

        foreach (User::role($roleNames->all())->get() as $user) {
            $user->unreadNotifications()
                ->where('type', self::class)
                ->where('data->reportable_type', $report->reportable_type)
                ->where('data->reportable_id', $report->reportable_id)
                ->update(['read_at' => now()]);
        }
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: string, message: string, url: string, report_id: int, reportable_type: string, reportable_id: int, count: int}
     */
    public function toArray(object $notifiable): array
    {
        $target = match ($this->report->reportable_type) {
            Thread::class => 'дискусија',
            Comment::class => 'коментар',
            default => 'содржина',
        };

        $reason = self::REASON_LABELS[$this->report->reason] ?? $this->report->reason;
        $countSuffix = $this->count > 1 ? " ({$this->count} пријави)" : '';

        return [
            'title' => 'Нова пријава',
            'message' => "Пријавен е {$target} — {$reason}{$countSuffix}.",
            'url' => route('report.index'),
            'report_id' => $this->report->id,
            'reportable_type' => $this->report->reportable_type,
            'reportable_id' => (int) $this->report->reportable_id,
            'count' => $this->count,
        ];
    }
}
