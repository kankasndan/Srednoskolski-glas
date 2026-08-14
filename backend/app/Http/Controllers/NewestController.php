<?php

namespace App\Http\Controllers;

use App\Http\Resources\ThreadResource;
use App\Services\Feed\NewestDiscussionsBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewestController extends Controller
{
    /**
     * Newest discussions for /newest.
     *
     * Eligible forums: all general + followed school forums.
     * When the user follows forums, results mix ~2 focused : 1 other (newest within each pool).
     * Guests / users with no follows: pure newest from general forums only.
     *
     * Query: page, time (day|week|month|year|all)
     */
    public function index(Request $request, NewestDiscussionsBuilder $builder): JsonResponse
    {
        $user = $request->user('web') ?? $request->user();

        $threads = $builder->paginate($request, $user);

        return ThreadResource::collection($threads)->response();
    }
}
