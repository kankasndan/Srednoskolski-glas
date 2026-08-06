<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Thread $thread): JsonResponse
    {
        $comment = Comment::query()->create([
            'thread_id' => $thread->id,
            'parent_id' => $request->integer('parent_id') ?: null,
            'user_id' => $request->user()->id,
            'content' => $request->string('content')->toString(),
        ]);

        $comment->load('user.studentData.school.city');
        $comment->setRelation('allReplies', collect());
        $comment->setAttribute('has_voted', false);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a comment's content (author only).
     *
     * PUT /api/comments/{comment}
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $comment->content = $request->string('content')->toString();
        $comment->edited_at = now();
        $comment->save();

        $comment->load('user.studentData.school.city');
        $comment->setRelation('allReplies', collect());

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
