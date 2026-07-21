<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ThreadResource;
use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ThreadController extends Controller
{
    use FiltersThreads;

    /**
     * Paginated threads for a single forum (infinite scroll).
     *
     * Route: GET /api/p/{forum}/threads
     * Query: page, sort (trending|top|newest|discussed), time (day|week|month|six-months|year|all)
     *
     * Returns 5 threads per page. Feed/FYP ranking is a separate endpoint later.
     */
    public function index(Forum $forum, Request $request): JsonResponse
    {
        $query = $forum->threads()
            ->with(['user.studentData.school.city', 'threadAttachment', 'forum'])
            ->withCount('comments');

        $this->applyThreadFilters($query, $request);

        $threads = $query->paginate($this->threadsPerPage())->withQueryString();

        return ThreadResource::collection($threads)->response();
    }

    /**
     * Show a single thread (scoped to its forum) with its nested comment tree.
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
            'data' => [
                'thread' => new ThreadResource($thread),
                'comments' => CommentResource::collection($comments),
            ],
        ]);
    }
}
