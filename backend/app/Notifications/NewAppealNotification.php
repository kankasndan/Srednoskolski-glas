<?php

namespace App\Notifications;

use App\Models\Appeal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewAppealNotification extends Notification
{
    use Queueable;

    public function __construct(public Appeal $appeal) {}

    public function via($notifiable)
    {
        return ['database']; // in-app/bell notifications only
    }

    public function toArray($notifiable)
    {
        return [
            'kind' => 'appeal',
            'title' => 'Нова жалба',
            'message' => "Жалба за санкција #{$this->appeal->id}",
            'url' => route('appeal.index'),
            'appeal_id' => $this->appeal->id,
            'status' => $this->appeal->status,
        ];
    }
}