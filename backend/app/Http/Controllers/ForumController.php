<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PresentsAuthor;
use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ForumController extends Controller
{
    use PresentsAuthor;

    /**
     * List forums for the feed sidebar: general (topic) forums and school forums
     * grouped by their city. City/school info comes from the linked school.
     */
    public function index(): JsonResponse
    {
        $forums = Forum::query()
            ->with('school.city')
            ->orderBy('name')
            ->get();

        $general = $forums
            ->where('type', 'general')
            ->map(fn (Forum $forum) => $this->presentForumCard($forum))
            ->values();

        $schoolsByCity = $forums
            ->where('type', 'school')
            ->filter(fn (Forum $forum) => $forum->school?->city !== null)
            ->groupBy(fn (Forum $forum) => $forum->school->city->id)
            ->map(fn ($group) => [
                'city' => [
                    'id' => $group->first()->school->city->id,
                    'name' => $group->first()->school->city->name,
                ],
                'forums' => $group
                    ->map(fn (Forum $forum) => $this->presentForumCard($forum))
                    ->values(),
            ])
            ->sortBy('city.name')
            ->values();

        return response()->json([
            'general' => $general,
            'schools_by_city' => $schoolsByCity,
        ]);
    }

    /**
     * Show a single forum (by slug) with its filtered/sorted threads.
     * Each thread carries its comment count, upvotes and (unless anonymous) author.
     */
    public function show(Forum $forum, Request $request): JsonResponse
    {
        $query = $forum->threads()
            ->with(['user.studentData.school.city', 'threadAttachment'])
            ->withCount('comments');

        if ($since = $this->timeWindow($request->query('time'))) {
            $query->where('created_at', '>=', $since);
        }

        $this->applySort($query, (string) $request->query('sort', 'trending'));

        $threads = $query->get()
            ->map(fn (Thread $thread) => $this->presentThread($thread))
            ->values();

        return response()->json([
            'forum' => $this->presentForum($forum->load('school.city')),
            'threads' => $threads,
        ]);
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->latest(),
            'top' => $query->orderByDesc('upvotes')->latest(),
            'discussed' => $query->orderByDesc('comments_count')->latest(),
            default => $query->orderByDesc('upvotes')->orderByDesc('comments_count')->latest(),
        };
    }

    private function timeWindow(?string $time): ?Carbon
    {
        return match ($time) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'six-months' => now()->subMonths(6),
            'year' => now()->subYear(),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function presentForumCard(Forum $forum): array
    {
        return [
            'id' => $forum->id,
            'name' => $forum->name,
            'slug' => $forum->slug,
            'type' => $forum->type,
            'school_id' => $forum->school_id,
            'imageUrl' => $forum->imageUrl,
            'threads_count' => $forum->threads_count,
            'members_count' => $forum->members_count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentForum(Forum $forum): array
    {
        return [
            'id' => $forum->id,
            'name' => $forum->name,
            'slug' => $forum->slug,
            'type' => $forum->type,
            'description' => $forum->description,
            'imageUrl' => $forum->imageUrl,
            'bannerUrl' => $forum->bannerUrl,
            'threads_count' => $forum->threads_count,
            'members_count' => $forum->members_count,
            'school' => $forum->school === null ? null : [
                'id' => $forum->school->id,
                'name' => $forum->school->name,
                'city' => $forum->school->city?->name,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentThread(Thread $thread): array
    {
        return [
            'id' => $thread->id,
            'title' => $thread->title,
            'description' => $thread->description,
            'upvotes' => $thread->upvotes,
            'views' => $thread->views,
            'is_anonymous' => $thread->is_anonymous,
            'comments_count' => $thread->comments_count,
            'created_at' => $thread->created_at,
            'author' => $thread->is_anonymous ? null : $this->presentAuthor($thread->user),
            'attachments' => $thread->threadAttachment
                ->map(fn ($attachment) => [
                    'url' => $attachment->url,
                    'type' => $attachment->slug,
                ])
                ->values(),
        ];
    }
}
