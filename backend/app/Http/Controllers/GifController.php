<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchGifsRequest;
use App\Services\Giphy\GiphyClient;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class GifController extends Controller
{
    /**
     * Trending GIFs when `q` is empty, search results otherwise.
     *
     * GET /api/gifs
     */
    public function index(SearchGifsRequest $request, GiphyClient $giphy): JsonResponse
    {
        try {
            $gifs = $giphy->search(trim((string) ($request->validated('q') ?? '')));
        } catch (RuntimeException $exception) {
            report($exception);

            abort(502, 'Не успеа вчитувањето на GIF-овите.');
        }

        return response()->json([
            'data' => $gifs,
        ]);
    }
}
