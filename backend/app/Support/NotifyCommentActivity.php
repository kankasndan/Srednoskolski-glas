<?php

namespace App\Support;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\CommentActivityNotification;
use Illuminate\Support\Facades\Notification;

final class NotifyCommentActivity
{
    /**
     * One bell item per recipient: mention, then reply, then thread author, then follower.
     * The commenter is never notified.
     *
     * @param  list<int>  $newlyMentionedIds
     */
    public function forCreatedComment(Comment $comment, array $newlyMentionedIds): void
    {
        $comment->loadMissing(['thread.forum', 'user', 'parent']);
        $thread = $comment->thread;

        if ($thread === null) {
            return;
        }

        $actorId = (int) $comment->user_id;
        $notified = [];

        $this->sendToIds(
            $newlyMentionedIds,
            $comment,
            CommentActivityNotification::REASON_MENTION,
            $actorId,
            $notified,
        );

        $parentAuthorId = (int) ($comment->parent?->user_id ?? 0);
        if ($parentAuthorId > 0) {
            $this->sendToIds(
                [$parentAuthorId],
                $comment,
                CommentActivityNotification::REASON_COMMENT_REPLY,
                $actorId,
                $notified,
            );
        }

        $authorId = (int) $thread->user_id;
        if ($authorId > 0 && $authorId !== $actorId && ! isset($notified[$authorId])) {
            $this->sendToIds(
                [$authorId],
                $comment,
                CommentActivityNotification::REASON_THREAD_COMMENT,
                $actorId,
                $notified,
            );
        }

        $followerIds = $thread->followers()
            ->whereNotNull('users.onboarding_completed_at')
            ->pluck('users.id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $this->sendToIds(
            $followerIds,
            $comment,
            CommentActivityNotification::REASON_FOLLOWED_THREAD,
            $actorId,
            $notified,
        );
    }

    /**
     * Editing a comment only notifies users who were newly mentioned.
     *
     * @param  list<int>  $newlyMentionedIds
     */
    public function forUpdatedComment(Comment $comment, array $newlyMentionedIds): void
    {
        $comment->loadMissing(['thread.forum', 'user']);
        $notified = [];

        $this->sendToIds(
            $newlyMentionedIds,
            $comment,
            CommentActivityNotification::REASON_MENTION,
            (int) $comment->user_id,
            $notified,
        );
    }

    /**
     * @param  list<int>  $userIds
     * @param  array<int, true>  $notified
     */
    private function sendToIds(
        array $userIds,
        Comment $comment,
        string $reason,
        int $actorId,
        array &$notified,
    ): void {
        $ids = [];

        foreach ($userIds as $userId) {
            $id = (int) $userId;
            if ($id <= 0 || $id === $actorId || isset($notified[$id])) {
                continue;
            }
            $ids[] = $id;
        }

        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return;
        }

        $users = User::query()
            ->whereIn('id', $ids)
            ->whereNotNull('onboarding_completed_at')
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new CommentActivityNotification($comment, $reason));

        foreach ($users as $user) {
            $notified[(int) $user->id] = true;
        }
    }
}
