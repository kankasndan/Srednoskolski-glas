<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CommentActivityNotification extends Notification
{
    use Queueable;

    public const REASON_MENTION = 'mention';

    public const REASON_COMMENT_REPLY = 'comment_reply';

    public const REASON_THREAD_COMMENT = 'thread_comment';

    public const REASON_FOLLOWED_THREAD = 'followed_thread_comment';

    public function __construct(
        public Comment $comment,
        public string $reason,
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
     *     comment_id: int,
     *     thread_id: int|null,
     *     expand_path: list<int>,
     *     actor_username: string|null,
     *     actor_image_url: string|null
     * }
     */
    public function toArray(object $notifiable): array
    {
        $this->comment->loadMissing(['thread.forum', 'user', 'parent']);

        $thread = $this->comment->thread;
        $forumSlug = $thread?->forum?->slug;
        $threadId = $thread?->id;
        $commentId = (int) $this->comment->id;
        $expandPath = $this->ancestorIds($this->comment);

        $url = '/feed';
        if ($forumSlug && $threadId) {
            $query = http_build_query(array_filter([
                'comment' => $commentId,
                'expand' => $expandPath === [] ? null : implode('.', $expandPath),
            ]));
            $url = "/p/{$forumSlug}/{$threadId}?{$query}#comment-{$commentId}";
        }

        $threadTitle = Str::limit(trim((string) ($thread?->title ?? '')), 80);
        $actorIsAnonymous = $thread !== null
            && $thread->is_anonymous
            && (int) $this->comment->user_id === (int) $thread->user_id;

        $actorUsername = $actorIsAnonymous
            ? null
            : $this->comment->user?->username;
        $actorImageUrl = $actorIsAnonymous
            ? null
            : $this->comment->user?->imageUrl;

        [$title, $message] = $this->copy($actorUsername, $threadTitle);

        return [
            'reason' => $this->reason,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'comment_id' => $commentId,
            'thread_id' => $threadId,
            'expand_path' => $expandPath,
            'actor_username' => $actorUsername,
            'actor_image_url' => $actorImageUrl,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function copy(?string $actorUsername, string $threadTitle): array
    {
        $who = filled($actorUsername) ? $actorUsername : 'Некој';
        $quoted = $threadTitle !== '' ? " „{$threadTitle}“" : '';

        return match ($this->reason) {
            self::REASON_MENTION => [
                'Спомнување',
                "{$who} те спомна во коментар{$quoted}.",
            ],
            self::REASON_COMMENT_REPLY => [
                'Одговор',
                "{$who} одговори на твојот коментар{$quoted}.",
            ],
            self::REASON_THREAD_COMMENT => [
                'Нов коментар',
                "{$who} коментираше на твојата дискусија{$quoted}.",
            ],
            default => [
                'Следена дискусија',
                "{$who} коментираше на дискусија што ја следиш{$quoted}.",
            ],
        };
    }

    /**
     * Ancestor comment ids from the thread root down to the parent of this comment.
     *
     * @return list<int>
     */
    private function ancestorIds(Comment $comment): array
    {
        $ids = [];
        $current = $comment;

        while ($current->parent_id) {
            $parent = Comment::query()
                ->withTrashed()
                ->find($current->parent_id);

            if ($parent === null) {
                break;
            }

            $ids[] = (int) $parent->id;
            $current = $parent;
        }

        return array_reverse($ids);
    }
}
