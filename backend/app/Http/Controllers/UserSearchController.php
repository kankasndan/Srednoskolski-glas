<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchUsersRequest;
use App\Http\Resources\UserMentionResource;
use App\Models\User;
use App\Support\LikeEscape;
use Illuminate\Http\JsonResponse;

class UserSearchController extends Controller
{
    public const LIMIT = 8;

    /**
     * Username-prefix autocomplete for @mentions in comments.
     *
     * GET /api/users/search?q=
     */
    public function index(SearchUsersRequest $request): JsonResponse
    {
        $q = trim((string) ($request->validated('q') ?? ''));
        $prefix = LikeEscape::startsWith($q);
        $viewer = $request->user();
        $usersTable = (new User)->getTable();
        $follows = $viewer->following();
        $pivotTable = $follows->getTable();
        $followerKey = $follows->getForeignPivotKeyName();
        $followingKey = $follows->getRelatedPivotKeyName();

        $users = User::query()
            ->whereNotNull('username')
            ->whereNotNull('onboarding_completed_at')
            ->whereKeyNot($viewer->id)
            ->when($q !== '', function ($query) use ($prefix): void {
                $query->where('username', 'like', $prefix);
            })
            ->orderByRaw(
                "case when exists (select 1 from {$pivotTable} where {$pivotTable}.{$followerKey} = ? and {$pivotTable}.{$followingKey} = {$usersTable}.id) then 0 else 1 end",
                [$viewer->id],
            )
            ->orderBy('username')
            ->limit(self::LIMIT)
            ->get(['id', 'username', 'imageUrl']);

        return UserMentionResource::collection($users)->response();
    }
}
