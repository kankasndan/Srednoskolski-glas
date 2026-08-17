<?php

namespace App\Support;

use App\Models\Comment;
use App\Models\User;

final class SyncCommentMentions
{
    /**
     * Replace stored mentions for a comment from `@username` tokens in its body.
     * Unknown names and self-mentions are ignored. No notifications in MVP.
     */
    public function handle(Comment $comment): void
    {
        $usernames = MentionParser::usernames($comment->content);
        $mentionedIds = $this->resolveMentionedUserIds($comment, $usernames);

        if ($mentionedIds === []) {
            $comment->mentions()->delete();

            return;
        }

        $comment->mentions()
            ->whereNotIn('mentioned_user_id', $mentionedIds)
            ->delete();

        foreach ($mentionedIds as $mentionedId) {
            $comment->mentions()->firstOrCreate([
                'mentioning_user_id' => $comment->user_id,
                'mentioned_user_id' => $mentionedId,
            ]);
        }
    }

    /**
     * @param  list<string>  $usernames
     * @return list<int>
     */
    private function resolveMentionedUserIds(Comment $comment, array $usernames): array
    {
        if ($usernames === []) {
            return [];
        }

        $lower = array_map(
            fn (string $username): string => mb_strtolower($username, 'UTF-8'),
            $usernames,
        );

        return User::query()
            ->whereNotNull('username')
            ->whereNotNull('onboarding_completed_at')
            ->whereKeyNot($comment->user_id)
            ->where(function ($query) use ($lower): void {
                foreach ($lower as $username) {
                    $query->orWhereRaw('LOWER(username) = ?', [$username]);
                }
            })
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
