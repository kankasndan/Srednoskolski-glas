<?php

namespace App\Support;

use App\Notifications\CommentActivityNotification;
use App\Notifications\NewFollowNotification;

final class StudentNotificationTypes
{
    /**
     * In-app bell types shown to students (never admin moderation).
     *
     * @return list<class-string>
     */
    public static function all(): array
    {
        return [
            CommentActivityNotification::class,
            NewFollowNotification::class,
        ];
    }
}
