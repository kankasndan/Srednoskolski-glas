<?php

namespace App\Http\Controllers;

use App\Http\Resources\ForumResource;
use App\Models\Forum;
use App\Models\School;
use App\Support\ViewThrottle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForumController extends Controller
{
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
     * Show a single forum by slug (banner / metadata only).
     *
     * Threads always come from GET /api/p/{slug}/threads (filters, page 1, scroll).
     * Pass track_view=1 when opening the forum page so visits count toward explore ranking.
     */
    public function show(Request $request, Forum $forum): JsonResponse
    {
        $forum->abortUnlessReadableBy($request->user('web') ?? $request->user());

        if ($request->boolean('track_view') && ViewThrottle::shouldIncrement($request, 'forum', $forum->id)) {
            $forum->increment('views');
            $forum->refresh();
        }

        $forum->load('school.city');

        return response()->json([
            'data' => [
                'forum' => (new ForumResource($forum))->resolve(),
            ],
        ]);
    }
}
