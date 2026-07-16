<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use Illuminate\Http\JsonResponse;

class ForumController extends Controller
{
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
            ->map(fn (Forum $forum) => $this->presentForum($forum))
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
                    ->map(fn (Forum $forum) => $this->presentForum($forum))
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
     * @return array<string, mixed>
     */
    private function presentForum(Forum $forum): array
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
}
