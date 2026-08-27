<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Models\Feedback;
use App\Notifications\NewFeedbackNotification;
use Illuminate\Http\JsonResponse;

class StoreFeedbackController extends Controller
{
    public function __invoke(StoreFeedbackRequest $request): JsonResponse
    {
        $feedback = Feedback::query()->create([
            'user_id' => $request->user()?->id,
            'rating' => $request->validated('rating'),
            'message' => $request->validated('message'),
        ]);

        NewFeedbackNotification::syncForFeedback($feedback);

        return response()->json([
            'data' => [
                'id' => $feedback->id,
            ],
        ], 201);
    }
}
