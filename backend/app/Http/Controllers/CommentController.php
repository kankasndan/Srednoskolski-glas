<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;

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
}
