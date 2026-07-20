<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Resources\ForumResource;
use App\Http\Resources\ThreadResource;
use App\Models\Forum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ForumController extends Controller
{
    use FiltersThreads;

    /**
     * List ALL forums for the sidebar (no pagination).
     * Topic forums under `general`, school forums grouped by city under `schools_by_city`.
     */
    public function index(): JsonResponse
    {
        $forums = Forum::query()
            ->with('school.city')
            ->orderBy('name')
            ->get();

        $general = $forums
            ->where('type', 'general')
            ->values()
            ->map(fn (Forum $forum) => (new ForumResource($forum))->card()->resolve())
            ->all();

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
                    ->values()
                    ->map(fn (Forum $forum) => (new ForumResource($forum))->card()->resolve())
                    ->all(),
            ])
            ->sortBy('city.name')
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'general' => $general,
                'schools_by_city' => $schoolsByCity,
            ],
        ]);
    }

    /**
     * Show a forum by slug, plus its threads (paginated, 5 per page).
     *
     * Query: ?page=1&sort=trending|top|newest|discussed&time=day|week|month|six-months|year|all
     *
     * For infinite scroll the frontend can also call GET /api/p/{slug}/threads?page=2.
     */
    public function show(Forum $forum, Request $request): JsonResponse
    {
        $forum->load('school.city');

        $query = $forum->threads()
            ->with(['user.studentData.school.city', 'threadAttachment', 'forum'])
            ->withCount('comments');

        $this->applyThreadFilters($query, $request);

        /** @var LengthAwarePaginator $threads */
        $threads = $query->paginate($this->threadsPerPage())->withQueryString();

        return response()->json([
            'data' => [
                'forum' => (new ForumResource($forum))->resolve(),
                'threads' => ThreadResource::collection($threads->items())->resolve(),
            ],
            'meta' => [
                'current_page' => $threads->currentPage(),
                'last_page' => $threads->lastPage(),
                'per_page' => $threads->perPage(),
                'total' => $threads->total(),
            ],
            'links' => [
                'first' => $threads->url(1),
                'last' => $threads->url($threads->lastPage()),
                'prev' => $threads->previousPageUrl(),
                'next' => $threads->nextPageUrl(),
            ],
        ]);
    }
}
