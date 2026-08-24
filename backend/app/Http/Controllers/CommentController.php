<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Thread;
use App\Models\Vote;
use App\Support\SyncCommentMentions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(
        StoreCommentRequest $request,
        Thread $thread,
        SyncCommentMentions $syncMentions,
    ): JsonResponse {
        $comment = DB::transaction(function () use ($request, $thread): Comment {
            $comment = Comment::forceCreate([
                'thread_id' => $thread->id,
                'parent_id' => $request->integer('parent_id') ?: null,
                'user_id' => $request->user()->id,
                'content' => $request->string('content')->toString(),
                'gif_url' => $request->validated('gif_url'),
            ]);

            Vote::addFor($request->user(), $comment);

            return $comment;
        });

        $syncMentions->handle($comment);
        $comment->load(Comment::authorWith());
        $comment->setAttribute('has_voted', true);
        $comment->setAttribute('replies_count', 0);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Direct replies for a comment (lazy-loaded from "view replies").
     *
     * GET /api/comments/{comment}/replies
     */
    public function replies(Request $request, Comment $comment): JsonResponse
    {
        $user = $request->user('web') ?? $request->user();
        $comment->loadMissing('thread.forum');
        $comment->thread?->forum?->abortUnlessReadableBy($user);

        $query = $comment->replies()
            ->visibleInThread()
            ->with(Comment::authorWith())
            ->withVisibleRepliesCount()
            ->oldest()
            ->orderBy('id');

        if ($user !== null) {
            $query->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
            ]);
        }

        return CommentResource::collection($query->get())->response();
    }

    /**
     * Update a comment's content (author only).
     *
     * PUT /api/comments/{comment}
     */
    public function update(
        UpdateCommentRequest $request,
        Comment $comment,
        SyncCommentMentions $syncMentions,
    ): JsonResponse {
        $comment->content = $request->string('content')->toString();
        $comment->edited_at = now();
        $comment->save();

        $syncMentions->handle($comment);
        $comment->load(Comment::authorWith());
        $comment->loadCount([
            'replies as replies_count' => fn ($replies) => $replies->visibleInThread(),
        ]);

        $userId = $request->user()->id;
        $comment->setAttribute(
            'has_voted',
            $comment->votes()->where('user_id', $userId)->exists(),
        );

        return (new CommentResource($comment))->response();
    }

    /**
     * Soft-delete a comment (author only). Remains visible as a tombstone when it has replies.
     *
     * DELETE /api/comments/{comment}
     */
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        abort_unless(
            (int) $request->user()->id === (int) $comment->user_id,
            403,
        );

        $comment->delete();

        return response()->json([
            'data' => [
                'deleted' => true,
            ],
        ]);
    }
}
