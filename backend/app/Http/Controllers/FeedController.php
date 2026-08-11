<?php

namespace App\Http\Controllers;

use App\Http\Resources\ThreadResource;
use App\Services\Feed\FeedBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * Cross-forum home feed for /feed.
     *
     * Default sort (`trending`) uses: candidate load → hot score (time decay +
     * velocity) → personal affinity → seen demotion → home/discovery/fresh mix →
     * forum diversity → pagination.
     *
     * Query: page, sort (trending|top|newest|discussed), time (day|week|month|six-months|year|all)
     */
    public function index(Request $request, FeedBuilder $feedBuilder): JsonResponse
    {
        $user = $request->user('web') ?? $request->user();

        $threads = $feedBuilder->paginate($request, $user);

        return ThreadResource::collection($threads)->response();
    }
}
