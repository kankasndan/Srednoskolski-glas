<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Comment;
use App\Models\Thread;
use App\Models\User;

/**
 * Shared guards for actions taken *on* a piece of content (voting, reporting,
 * poll votes, hiding). Route binding already hides soft-deleted models, but it
 * says nothing about the thread or forum they live in, so an action could still
 * be aimed at content the caller can no longer see.
 */
trait ChecksContentAccess
{
    protected function assertThreadAccessible(?Thread $thread, ?User $user): void
    {
        abort_if($thread === null || $thread->trashed(), 404);

        $thread->forum?->abortUnlessReadableBy($user);
    }

    protected function assertCommentAccessible(Comment $comment, ?User $user): void
    {
        abort_if($comment->trashed(), 404);

        $this->assertThreadAccessible($comment->thread, $user);
    }
}
