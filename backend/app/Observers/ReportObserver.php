<?php

namespace App\Observers;

use App\Models\Report;
use App\Models\User;
use App\Notifications\AdminNotification;

class ReportObserver
{
    /**
     * Handle the Report "created" event.
     */
    public function created(Report $report)
    {
        $forum = $report->post->forum; // adjust to match your actual relation chain

        $recipients = collect();

        if ($forum->moderator) { // uses the belongsTo relationship on Forum
            $recipients->push($forum->moderator);
        }

        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        $recipients = $recipients->merge($admins)->unique('id');

        foreach ($recipients as $recipient) {
            $recipient->notify(new AdminNotification(
                'report',
                $report->id,
                "New report submitted in {$forum->name} by {$report->user->username}",
                route('admin.reports.show', $report->id)
            ));
        }
    }

    /**
     * Handle the Report "updated" event.
     */
    public function updated(Report $report): void
    {
        //
    }

    /**
     * Handle the Report "deleted" event.
     */
    public function deleted(Report $report): void
    {
        //
    }

    /**
     * Handle the Report "restored" event.
     */
    public function restored(Report $report): void
    {
        //
    }

    /**
     * Handle the Report "force deleted" event.
     */
    public function forceDeleted(Report $report): void
    {
        //
    }
}
