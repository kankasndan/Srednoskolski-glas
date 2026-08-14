<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersThreads;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ForumResource;
use App\Http\Resources\ProfileCommentResource;
use App\Http\Resources\PublicUserResource;
use App\Http\Resources\ThreadResource;
use App\Models\City;
use App\Models\Forum;
use App\Models\School;
use App\Models\StudentData;
use App\Models\User;
use App\Models\Vocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    use FiltersThreads;

    /**
     * @var array<string, int>
     */
    private const GRADE_MAP = [
        'Прва' => 1,
        'Втора' => 2,
        'Трета' => 3,
        'Четврта' => 4,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
    ];

    private const SCHOOL_SEPARATOR = '|';

    /**
     * Update the authenticated user's avatar and/or school information.
     * Username cannot be changed after onboarding.
     *
     * PUT /api/me
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if (array_key_exists('image_url', $validated)) {
            $imageUrl = $validated['image_url'];

            if ($imageUrl === null || $imageUrl === '') {
                $defaults = config('avatars.defaults', []);
                $imageUrl = $defaults[0] ?? null;
            }

            $user->imageUrl = $imageUrl;
            $user->save();
        }

        if (
            array_key_exists('school', $validated)
            && array_key_exists('area', $validated)
            && array_key_exists('year', $validated)
            && filled($validated['school'])
            && filled($validated['area'])
            && filled($validated['year'])
        ) {
            $this->updateStudentData($user, $validated);
        }

        return response()->json([
            'user' => $user->fresh([
                'studentData.school.city',
                'studentData.school.forum',
                'studentData.vocation',
            ]),
        ]);
    }

    /**
     * Lightweight tab badges for the profile page.
     *
     * GET /api/me/counts
     */
    public function counts(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'threads' => $user->threads()->count(),
                'comments' => $user->comments()->count(),
                'followed_forums' => $user->forums()->count(),
                'followed_threads' => $user->followedThreads()->count(),
                'following_users' => $user->following()->count(),
            ],
        ]);
    }

    /**
     * Threads created by the authenticated user (newest first).
     *
     * GET /api/me/threads
     */
    public function threads(Request $request): JsonResponse
    {
        $user = $request->user();

        $threads = $user->threads()
            ->with($this->threadListWith($user))
            ->withCount('comments')
            ->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
            ])
            ->latest()
            ->limit(50)
            ->get();

        return ThreadResource::collection($threads)->response();
    }

    /**
     * Comments written by the authenticated user, with parent thread context.
     *
     * GET /api/me/comments
     */
    public function comments(Request $request): JsonResponse
    {
        $user = $request->user();

        $comments = $user->comments()
            ->with([
                'thread.forum',
            ])
            ->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
            ])
            ->latest()
            ->limit(50)
            ->get();

        return ProfileCommentResource::collection($comments)->response();
    }

    /**
     * Forums the authenticated user follows.
     *
     * GET /api/me/followed-forums
     */
    public function followedForums(Request $request): JsonResponse
    {
        $forums = $request->user()
            ->forums()
            ->orderBy('name')
            ->limit(100)
            ->get();

        $forums->each(fn (Forum $forum) => $forum->setAttribute('is_following', true));

        return ForumResource::collection($forums)->response();
    }

    /**
     * Threads the authenticated user follows (newest follow first).
     *
     * GET /api/me/followed-threads
     */
    public function followedThreads(Request $request): JsonResponse
    {
        $user = $request->user();

        $threads = $user->followedThreads()
            ->with($this->threadListWith($user))
            ->withCount('comments')
            ->withExists([
                'votes as has_voted' => fn ($votes) => $votes->where('user_id', $user->id),
                'followers as is_following' => fn ($followers) => $followers->where('users.id', $user->id),
            ])
            ->orderByDesc('thread_follows.created_at')
            ->limit(50)
            ->get();

        return ThreadResource::collection($threads)->response();
    }

    /**
     * Users the authenticated user follows.
     *
     * GET /api/me/following-users
     */
    public function followingUsers(Request $request): JsonResponse
    {
        $users = $request->user()
            ->following()
            ->whereNotNull('username')
            ->whereNotNull('onboarding_completed_at')
            ->with([
                'studentData.school.city',
                'studentData.school.forum',
                'studentData.vocation',
            ])
            ->orderBy('username')
            ->limit(100)
            ->get();

        return PublicUserResource::collection($users)->response();
    }

    /**
     * @param  array{school: string, area: string, year: string}  $validated
     */
    private function updateStudentData(User $user, array $validated): void
    {
        $user->loadMissing(['studentData.school.forum']);

        ['school' => $schoolName, 'city' => $cityName] = $this->parseSchoolSelection($validated['school']);
        $school = $this->resolveSchool($cityName, $schoolName);
        $vocation = Vocation::query()->where('name', $validated['area'])->first();

        if ($vocation === null) {
            throw ValidationException::withMessages([
                'area' => ['Избраното подрачје не е валидно.'],
            ]);
        }

        $grade = self::GRADE_MAP[$validated['year']] ?? null;

        if ($grade === null) {
            throw ValidationException::withMessages([
                'year' => ['Избраната година не е валидна.'],
            ]);
        }

        $previousSchool = $user->studentData?->school;

        StudentData::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'school_id' => $school->id,
                'vocation_id' => $vocation->id,
                'grade' => $grade,
            ],
        );

        $this->syncSchoolForum($user, $previousSchool, $school);
    }

    /**
     * @return array{school: string, city: string}
     */
    private function parseSchoolSelection(string $value): array
    {
        $separatorPosition = mb_strrpos($value, self::SCHOOL_SEPARATOR);

        if ($separatorPosition === false) {
            return ['school' => trim($value), 'city' => ''];
        }

        return [
            'school' => trim(mb_substr($value, 0, $separatorPosition)),
            'city' => trim(mb_substr($value, $separatorPosition + mb_strlen(self::SCHOOL_SEPARATOR))),
        ];
    }

    private function resolveSchool(string $cityName, string $schoolName): School
    {
        $city = City::query()->where('name', $cityName)->first();

        if ($city === null) {
            throw ValidationException::withMessages([
                'school' => ['Избраното училиште не е валидно.'],
            ]);
        }

        $school = School::query()
            ->where('city_id', $city->id)
            ->where('name', $schoolName)
            ->first();

        if ($school === null) {
            throw ValidationException::withMessages([
                'school' => ['Избраното училиште не е валидно.'],
            ]);
        }

        return $school;
    }

    private function syncSchoolForum(User $user, ?School $previous, School $next): void
    {
        if ($previous !== null && (int) $previous->id === (int) $next->id) {
            $this->followSchoolForum($user, $next);

            return;
        }

        if ($previous?->forum !== null) {
            $forum = $previous->forum;
            $detached = $user->forums()->detach($forum->id);

            if ($detached > 0 && $forum->members_count > 0) {
                $forum->decrement('members_count');
            }
        }

        $this->followSchoolForum($user, $next);
    }

    private function followSchoolForum(User $user, School $school): void
    {
        $forum = $school->forum;

        if ($forum === null) {
            return;
        }

        $sync = $user->forums()->syncWithoutDetaching([$forum->id]);

        if ($sync['attached'] !== []) {
            $forum->increment('members_count');
        }
    }
}
