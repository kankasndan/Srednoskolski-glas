<?php

namespace App\Http\Controllers;

use App\Facades\Media;
use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ThreadResource;
use App\Models\Comment;
use App\Models\Forum;
use App\Models\Poll;
use App\Models\Thread;
use App\Models\ThreadView;
use App\Support\HtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
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
            ->with($this->threadListWith($user))
            ->withCount('comments');

        $this->applyHasVoted($query, $user);
        $this->applyThreadFilters($query, $request);

        $threads = $query->paginate($this->threadsPerPage())->withQueryString();

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
        $files = $this->normalizeUploadedFiles($request->file('files', []));

        $thread = DB::transaction(function () use ($validated, $user, $files): Thread {
            $thread = Thread::query()->create([
                'title' => $validated['title'],
                'description' => HtmlSanitizer::clean($validated['description'] ?? ''),
                'forum_id' => $validated['forum_id'],
                'user_id' => $user->id,
                'is_anonymous' => (bool) ($validated['is_anonymous'] ?? false),
                'upvotes' => 0,
                'views' => 0,
            ]);

            Forum::query()->whereKey($thread->forum_id)->increment('threads_count');

            $this->attachUploadedFiles($thread, $files);
            $this->attachLink($thread, $validated['link'] ?? null);

            if (! empty($validated['poll']['question'])) {
                $poll = Poll::query()->create([
                    'thread_id' => $thread->id,
                    'question' => $validated['poll']['question'],
                    'ends_at' => now()->addDays((int) $validated['poll']['duration_days']),
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

        return (new ThreadResource($this->loadThreadResource($thread)))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update a thread (author only): title/description and optional attachments.
     *
     * PUT|POST /api/threads/{thread}
     * Prefer POST multipart/form-data when uploading or removing files.
     */
    public function update(UpdateThreadRequest $request, Thread $thread): JsonResponse
    {
        $validated = $request->validated();
        $files = $this->normalizeUploadedFiles($request->file('files', []));

        /** @var list<int> $removeIds */
        $removeIds = array_values(array_map(
            static fn ($id) => (int) $id,
            $validated['remove_attachment_ids'] ?? [],
        ));

        DB::transaction(function () use ($thread, $validated, $files, $removeIds): void {
            $thread->title = $validated['title'];
            $thread->description = HtmlSanitizer::clean($validated['description'] ?? '');
            $thread->edited_at = now();
            $thread->save();

            $this->removeAttachments($thread, $removeIds);
            $this->attachUploadedFiles($thread, $files);
            $this->attachLink($thread, $validated['link'] ?? null);
            $this->syncPoll($thread, $validated);
        });

        return (new ThreadResource($this->loadThreadResource($thread->refresh())))->response();
    }

    /**
     * Create, update, or remove the thread poll from an update payload.
     * Omitting poll leaves the existing one unchanged.
     *
     * @param  array<string, mixed>  $validated
     */
    private function syncPoll(Thread $thread, array $validated): void
    {
        if (! empty($validated['remove_poll'])) {
            $thread->poll?->delete();

            return;
        }

        if (empty($validated['poll']['question'])) {
            return;
        }

        $pollData = $validated['poll'];
        $endsAt = now()->addDays((int) $pollData['duration_days']);
        $labels = array_values($pollData['options']);
        $optionIds = array_values($pollData['option_ids'] ?? []);

        $poll = $thread->poll;

        if ($poll === null) {
            $poll = Poll::query()->create([
                'thread_id' => $thread->id,
                'question' => $pollData['question'],
                'ends_at' => $endsAt,
            ]);

            foreach ($labels as $index => $label) {
                $poll->options()->create([
                    'label' => $label,
                    'position' => $index,
                ]);
            }

            return;
        }

        $poll->question = $pollData['question'];
        $poll->ends_at = $endsAt;
        $poll->save();

        $keptIds = [];

        foreach ($labels as $index => $label) {
            $optionId = isset($optionIds[$index]) && is_numeric($optionIds[$index])
                ? (int) $optionIds[$index]
                : null;

            if ($optionId !== null) {
                $option = $poll->options()->whereKey($optionId)->first();
                if ($option !== null) {
                    $option->label = $label;
                    $option->position = $index;
                    $option->save();
                    $keptIds[] = $option->id;

                    continue;
                }
            }

            $created = $poll->options()->create([
                'label' => $label,
                'position' => $index,
            ]);
            $keptIds[] = $created->id;
        }

        $poll->options()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * Soft-delete a thread (author only). Cascades soft-delete to comments.
     *
     * DELETE /api/threads/{thread}
     */
    public function destroy(Request $request, Thread $thread): JsonResponse
    {
        abort_unless(
            (int) $request->user()->id === (int) $thread->user_id,
            403,
        );

        DB::transaction(function () use ($thread): void {
            $forumId = $thread->forum_id;
            $thread->delete();

            Forum::query()
                ->whereKey($forumId)
                ->where('threads_count', '>', 0)
                ->decrement('threads_count');
        });

        return response()->json([
            'data' => [
                'deleted' => true,
            ],
        ]);
    }

    /**
     * Show a single thread (scoped to its forum) with top-level comments.
     * Replies are loaded separately via GET /api/comments/{comment}/replies.
     * Records a per-user view (for feed personalization) and bumps the public views counter.
     */
    public function show(Forum $forum, Thread $thread, Request $request): JsonResponse
    {
        if ($thread->forum_id !== $forum->id) {
            throw new NotFoundHttpException('Thread does not belong to this forum.');
        }

        $user = $request->user('web') ?? $request->user();

        if ($this->shouldTrackThreadView($request)) {
            $thread->increment('views');

            if ($user !== null) {
                $now = now();
                ThreadView::query()->upsert(
                    [[
                        'user_id' => $user->id,
                        'thread_id' => $thread->id,
                        'last_viewed_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]],
                    uniqueBy: ['user_id', 'thread_id'],
                    update: ['last_viewed_at', 'updated_at'],
                );
            }
        }

        $threadQuery = Thread::query()->whereKey($thread->id);
        $this->applyHasVoted($threadQuery, $user);
        $this->applyIsFollowing($threadQuery, $user);
        $thread = $threadQuery
            ->with($this->threadListWith($user))
            ->withCount('comments')
            ->firstOrFail();

        $comments = $this->loadTopLevelComments($thread, $user, (string) $request->query('sort', 'best'));

        return response()->json([
            'data' => [
                'thread' => new ThreadResource($thread),
                'comments' => CommentResource::collection($comments),
            ],
        ]);
    }

    /**
     * Sort/refetch requests pass track_view=0 so they do not inflate the counter.
     */
    private function shouldTrackThreadView(Request $request): bool
    {
        if (! $request->exists('track_view')) {
            return true;
        }

        return $request->boolean('track_view');
    }

    /**
     * Top-level comments only. Each row includes replies_count for the "view replies" button.
     */
    private function loadTopLevelComments(Thread $thread, mixed $user, string $sort)
    {
        $commentsQuery = $thread->comments()
            ->withTrashed()
            ->whereNull('parent_id')
            ->visibleInThread()
            ->with(Comment::authorWith())
            ->withVisibleRepliesCount();

        $this->applyCommentSort($commentsQuery, $sort);
        $this->applyHasVoted($commentsQuery, $user);

        return $commentsQuery->get();
    }

    /**
     * Top-level comment order for thread detail.
     *
     * Query: sort=best|newest|oldest (default: best)
     */
    private function applyCommentSort($query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->latest('created_at')->orderByDesc('id'),
            'oldest' => $query->oldest('created_at')->orderBy('id'),
            default => $query->orderByDesc('upvotes')->latest('created_at')->orderByDesc('id'),
        };
    }

    /**
     * @param  UploadedFile|array<int, UploadedFile|null>|null  $files
     * @return list<UploadedFile>
     */
    private function normalizeUploadedFiles(UploadedFile|array|null $files): array
    {
        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter(
            $files,
            static fn ($file) => $file instanceof UploadedFile,
        ));
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    private function attachUploadedFiles(Thread $thread, array $files): void
    {
        foreach ($files as $file) {
            $stored = Media::upload($file, "threads/{$thread->id}");

            $thread->threadAttachment()->create([
                'url' => $stored->url,
                'slug' => $stored->type,
                'provider' => $stored->provider,
                'file_id' => $stored->id,
            ]);
        }
    }

    private function attachLink(Thread $thread, ?string $link): void
    {
        if ($link === null || $link === '') {
            return;
        }

        $thread->threadAttachment()->create([
            'url' => $link,
            'slug' => 'link',
            'provider' => 'none',
            'file_id' => null,
        ]);
    }

    /**
     * @param  list<int>  $removeIds
     */
    private function removeAttachments(Thread $thread, array $removeIds): void
    {
        if ($removeIds === []) {
            return;
        }

        $toRemove = $thread->threadAttachment()
            ->whereIn('id', $removeIds)
            ->get();

        foreach ($toRemove as $attachment) {
            $attachment->deleteFile();
            $attachment->delete();
        }
    }

    private function loadThreadResource(Thread $thread): Thread
    {
        $user = auth('web')->user() ?? Auth::user();

        $query = Thread::query()->whereKey($thread->id);
        $this->applyHasVoted($query, $user);
        $this->applyIsFollowing($query, $user);

        return $query
            ->with($this->threadListWith($user))
            ->withCount('comments')
            ->firstOrFail();
    }
}
