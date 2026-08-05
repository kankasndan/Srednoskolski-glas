<?php

namespace App\Http\Controllers;

use App\Facades\Media;
use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Requests\StoreThreadRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ThreadResource;
use App\Models\Forum;
use App\Models\Poll;
use App\Models\Thread;
use App\Models\ThreadView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
            ->with([
                'user.studentData.school.city',
                'threadAttachment',
                'forum',
                'poll.options' => fn ($q) => $q->withCount('votes'),
                'poll.votes',
            ])
            ->withCount('comments');

        $this->applyHasVoted($query, $user);
        $this->applyThreadFilters($query, $request);

        $threads = $query->paginate($this->threadsPerPage())->withQueryString();

        $threads->getCollection()->each(function (Thread $thread): void {
            if ($thread->poll) {
                $thread->poll->loadCount('votes');
            }
        });

        return ThreadResource::collection($threads)->response();
    }

    /**
     * Create a new discussion thread (auth required).
     *
     * POST /api/threads  (multipart/form-data)
     */
    public function store(StoreThreadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        /** @var list<UploadedFile> $files */
        $files = $request->file('files', []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        $thread = DB::transaction(function () use ($validated, $user, $files): Thread {
            $thread = Thread::query()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'forum_id' => $validated['forum_id'],
                'user_id' => $user->id,
                'is_anonymous' => (bool) ($validated['is_anonymous'] ?? false),
                'upvotes' => 0,
                'views' => 0,
            ]);

            Forum::query()->whereKey($thread->forum_id)->increment('threads_count');

            foreach ($files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $stored = Media::upload($file, "threads/{$thread->id}");

                $thread->threadAttachment()->create([
                    'url' => $stored->url,
                    'slug' => $stored->type,
                    'provider' => $stored->provider,
                    'file_id' => $stored->id,
                ]);
            }

            if (! empty($validated['link'])) {
                $thread->threadAttachment()->create([
                    'url' => $validated['link'],
                    'slug' => 'link',
                    'provider' => 'none',
                    'file_id' => null,
                ]);
            }

            if (! empty($validated['poll']['question'])) {
                $poll = Poll::query()->create([
                    'thread_id' => $thread->id,
                    'question' => $validated['poll']['question'],
                    'ends_at' => now()->addDays(3),
                ]);

                foreach (array_values($validated['poll']['options']) as $index => $label) {
                    $poll->options()->create([
                        'label' => $label,
                        'position' => $index,
                    ]);
                }
            }

            return $thread;
        });

        $thread->load([
            'user.studentData.school.city',
            'forum',
            'threadAttachment',
            'poll.options' => fn ($q) => $q->withCount('votes'),
            'poll.votes',
        ])->loadCount('comments');

        if ($thread->poll) {
            $thread->poll->loadCount('votes');
        }

        return (new ThreadResource($thread))
            ->response()
            ->setStatusCode(201);
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
            ->with([
                'user.studentData.school.city',
                'forum',
                'threadAttachment',
                'poll.options' => fn ($q) => $q->withCount('votes'),
                'poll.votes',
            ])
            ->withCount('comments')
            ->firstOrFail();

        if ($thread->poll) {
            $thread->poll->loadCount('votes');
        }

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
