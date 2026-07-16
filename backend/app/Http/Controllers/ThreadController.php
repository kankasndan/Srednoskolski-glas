<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PresentsAuthor;
use App\Models\Comment;
use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ThreadController extends Controller
{
    use PresentsAuthor;

    /**
     * Show a single thread (scoped to its forum) with its fully nested comment tree.
     */
    public function show(Forum $forum, Thread $thread): JsonResponse
    {
        if ($thread->forum_id !== $forum->id) {
            throw new NotFoundHttpException('Thread does not belong to this forum.');
        }

        $thread->load(['user.studentData.school.city', 'forum', 'threadAttachment'])
            ->loadCount('comments');

        $comments = $thread->comments()
            ->whereNull('parent_id')
            ->with(['user.studentData.school.city', 'allReplies'])
            ->latest()
            ->get();

        return response()->json([
            'thread' => [
                'id' => $thread->id,
                'title' => $thread->title,
                'description' => $thread->description,
                'upvotes' => $thread->upvotes,
                'views' => $thread->views,
                'is_anonymous' => $thread->is_anonymous,
                'comments_count' => $thread->comments_count,
                'created_at' => $thread->created_at,
                'forum' => $thread->forum === null ? null : [
                    'id' => $thread->forum->id,
                    'name' => $thread->forum->name,
                    'slug' => $thread->forum->slug,
                ],
                'author' => $thread->is_anonymous ? null : $this->presentAuthor($thread->user),
                'attachments' => $thread->threadAttachment
                    ->map(fn ($attachment) => [
                        'url' => $attachment->url,
                        'type' => $attachment->slug,
                    ])
                    ->values(),
            ],
            'comments' => $comments
                ->map(fn (Comment $comment) => $this->presentComment($comment))
                ->values(),
        ]);
    }

    /**
     * Recursively shape a comment and its replies.
     *
     * @return array<string, mixed>
     */
    private function presentComment(Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'parent_id' => $comment->parent_id,
            'created_at' => $comment->created_at,
            'author' => $this->presentAuthor($comment->user),
            'replies' => $comment->allReplies
                ->map(fn (Comment $reply) => $this->presentComment($reply))
                ->values(),
        ];
    }
}
