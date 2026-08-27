<?php

namespace App\Notifications;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class NewFeedbackNotification extends Notification
{
    use Queueable;

    public function __construct(public Feedback $feedback) {}

    /**
     * One unread bell item per submission. Feedback is not stacked with a counter.
     */
    public static function syncForFeedback(Feedback $feedback): void
    {
        $staff = self::staff();

        if ($staff->isEmpty()) {
            return;
        }

        $feedback->loadMissing('user');
        $notification = new self($feedback);

        foreach ($staff as $user) {
            if ((int) $user->id === (int) $feedback->user_id) {
                continue;
            }

            $alreadyNotified = $user->unreadNotifications()
                ->where('type', self::class)
                ->where('data->feedback_id', $feedback->id)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $user->notify($notification);
        }
    }

    public static function markTargetRead(Feedback $feedback): void
    {
        $staff = self::staff();

        if ($staff->isEmpty()) {
            return;
        }

        foreach ($staff as $user) {
            $user->unreadNotifications()
                ->where('type', self::class)
                ->where('data->feedback_id', $feedback->id)
                ->update(['read_at' => now()]);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private static function staff()
    {
        $roleNames = Role::query()
            ->whereIn('name', ['super_admin', 'admin', 'moderator'])
            ->pluck('name');

        if ($roleNames->isEmpty()) {
            return collect();
        }

        return User::role($roleNames->all())->get();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: string, message: string, url: string, feedback_id: int, user_id: int|null}
     */
    public function toArray(object $notifiable): array
    {
        $this->feedback->loadMissing('user');

        $username = $this->feedback->user?->username ?? 'Гостин';
        $stars = str_repeat('★', $this->feedback->rating).str_repeat('☆', 5 - $this->feedback->rating);

        return [
            'title' => 'Ново мислење',
            'message' => "{$username} оцени {$stars} ({$this->feedback->rating}/5).",
            'url' => route('feedback.show', $this->feedback),
            'feedback_id' => $this->feedback->id,
            'user_id' => $this->feedback->user_id,
        ];
    }
}
