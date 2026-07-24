<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ThreadResource;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\ThreadView;
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
     * Returns 5 threads per page.
     */
    public function index(Forum $forum, Request $request): JsonResponse
    {
        $user = $request->user('web') ?? $request->user();

        $query = $forum->threads()
            ->with(['user.studentData.school.city', 'threadAttachment', 'forum'])
            ->withCount('comments');

        $this->applyHasVoted($query, $user);
        $this->applyThreadFilters($query, $request);

        $threads = $query->paginate($this->threadsPerPage())->withQueryString();

        return ThreadResource::collection($threads)->response();
    }

    /**
     * Show a single thread (scoped to its forum) with its nested comment tree.
     * Records a per-user view (for feed personalization) and bumps the public views counter.
     */
    public function show(Forum $forum, Thread $thread, Request $request): JsonResponse
    {
        if ($thread->forum_id !== $forum->id) {
            throw new NotFoundHttpException('Thread does not belong to this forum.');
        }

        $thread->increment('views');
        $thread->refresh();

        $user = $request->user('web') ?? $request->user();

        if ($user !== null) {
            ThreadView::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'thread_id' => $thread->id,
                ],
                [
                    'last_viewed_at' => now(),
                ],
            );
        }

        $threadQuery = Thread::query()->whereKey($thread->id);
        $this->applyHasVoted($threadQuery, $user);
        $thread = $threadQuery
            ->with(['user.studentData.school.city', 'forum', 'threadAttachment'])
            ->withCount('comments')
            ->firstOrFail();

        $commentsQuery = $thread->comments()
            ->whereNull('parent_id')
            ->with(['user.studentData.school.city', 'allReplies'])
            ->latest();
        $this->applyHasVoted($commentsQuery, $user);
        $comments = $commentsQuery->get();

        return response()->json([
            'data' => [
                'thread' => new ThreadResource($thread),
                'comments' => CommentResource::collection($comments),
            ],
        ]);
    }
}
