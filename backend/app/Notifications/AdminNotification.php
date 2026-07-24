<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification
{
    use Queueable;

    protected string $source;
    protected int $sourceId;
    protected string $message;
    protected string $link;


    /**
     * Create a new notification instance.
     */
    public function __construct(string $source, int $sourceId, string $message, string $link)
    {
        $this->source = $source;
        $this->sourceId = $sourceId;
        $this->message = $message;
        $this->link = $link;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            "source" => $this->source,
            "source_id" => $this->sourceId,
            "message" => $this->message,
            "link" => $this->link,

        ]
    }
}
