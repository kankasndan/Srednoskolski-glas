<?php

namespace App\Http\Controllers;

use App\Facades\Media;
use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ThreadResource;
use App\Models\Forum;
use App\Models\Poll;
use App\Models\Thread;
use App\Models\ThreadView;
use App\Support\HtmlSanitizer;
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
        });

        return (new ThreadResource($this->loadThreadResource($thread->refresh())))->response();
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
     * Show a single thread (scoped to its forum) with its nested comment tree.
     * Records a per-user view (for feed personalization) and bumps the public views counter.
     */
    public function show(Forum $forum, Thread $thread, Request $request): JsonResponse
    {
        if ($thread->forum_id !== $forum->id) {
            throw new NotFoundHttpException('Thread does not belong to this forum.');
        }

        $thread->increment('views');

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
            ->with($this->threadListWith($user))
            ->withCount('comments')
            ->firstOrFail();

        // Soft-deleted top-level comments stay visible only when they still have live replies.
        $commentsQuery = $thread->comments()
            ->withTrashed()
            ->whereNull('parent_id')
            ->where(function ($query): void {
                $query->whereNull('comments.deleted_at')
                    ->orWhereHas('replies', fn ($replies) => $replies->withoutTrashed());
            })
            ->with(['user.studentData.school.city', 'user.studentData.school.forum', 'allReplies']);

        $this->applyCommentSort($commentsQuery, (string) $request->query('sort', 'best'));
        $this->applyHasVoted($commentsQuery, $user);
        $comments = $commentsQuery->get();

        return response()->json([
            'data' => [
                'thread' => new ThreadResource($thread),
                'comments' => CommentResource::collection($comments),
            ],
        ]);
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
            // best: highest upvotes, then newest as tiebreaker
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
        $user = auth('web')->user() ?? auth()->user();

        $thread->load($this->threadListWith($user))->loadCount('comments');

        return $thread;
    }
}
