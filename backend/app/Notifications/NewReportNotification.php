<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReportNotification extends Notification
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Ново пријавување',
            'message' => "Пријавено: {$this->report->reason}",
            'url' => route('report.index'),
            'report_id' => $this->report->id,
        ];
    }
}