<?php

namespace App\Notifications;

use App\Models\Appeal;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class NewAppealNotification extends Notification
{
    use Queueable;

    public function __construct(public Appeal $appeal) {}

    /**
     * One unread bell item per appeal. Appeals are not stacked with a counter.
     */
    public static function syncForAppeal(Appeal $appeal): void
    {
        $staff = self::staff();

        if ($staff->isEmpty()) {
            return;
        }

        $appeal->loadMissing(['user', 'sanction']);
        $notification = new self($appeal);

        foreach ($staff as $user) {
            $alreadyNotified = $user->unreadNotifications()
                ->where('type', self::class)
                ->where('data->appeal_id', $appeal->id)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $user->notify($notification);
        }
    }

    public static function markTargetRead(Appeal $appeal): void
    {
        $staff = self::staff();

        if ($staff->isEmpty()) {
            return;
        }

        foreach ($staff as $user) {
            $user->unreadNotifications()
                ->where('type', self::class)
                ->where('data->appeal_id', $appeal->id)
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
     * @return array{title: string, message: string, url: string, appeal_id: int, user_id: int}
     */
    public function toArray(object $notifiable): array
    {
        $this->appeal->loadMissing(['user', 'sanction']);

        $username = $this->appeal->user?->username ?? 'Корисник';
        $kind = match ($this->appeal->sanction?->type) {
            'warning' => 'предупредување',
            'permanent_ban' => 'трајна забрана',
            '7-day', 'custom' => 'забрана',
            default => 'санкција',
        };

        return [
            'title' => 'Нова жалба',
            'message' => "{$username} поднесе жалба против {$kind}.",
            'url' => route('appeal.show', $this->appeal),
            'appeal_id' => $this->appeal->id,
            'user_id' => (int) $this->appeal->user_id,
        ];
    }
}
