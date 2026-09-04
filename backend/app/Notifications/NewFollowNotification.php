<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollowNotification extends Notification
{
    use Queueable;

    public const REASON = 'new_follow';

    public function __construct(
        public User $follower,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{
     *     reason: string,
     *     title: string,
     *     message: string,
     *     url: string,
     *     actor_username: string|null,
     *     actor_image_url: string|null
     * }
     */
    public function toArray(object $notifiable): array
    {
        $username = $this->follower->username;
        $who = filled($username) ? $username : 'Некој';
        $url = filled($username) ? '/u/'.$username : '/feed';

        return [
            'reason' => self::REASON,
            'title' => 'Нов следбеник',
            'message' => "{$who} почна да те следи.",
            'url' => $url,
            'actor_username' => filled($username) ? $username : null,
            'actor_image_url' => $this->follower->imageUrl,
        ];
    }
}
