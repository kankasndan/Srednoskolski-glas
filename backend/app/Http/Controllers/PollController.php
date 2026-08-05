<?php

namespace App\Http\Controllers;

use App\Http\Resources\PollResource;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PollController extends Controller
{
    /**
     * Cast a single vote on a poll option (one choice per user).
     *
     * POST /api/polls/{poll}/vote
     * Body: { "poll_option_id": 1 }
     */
    public function vote(Request $request, Poll $poll): JsonResponse
    {
        $validated = $request->validate([
            'poll_option_id' => ['required', 'integer', 'exists:poll_options,id'],
        ]);

        if ($poll->hasEnded()) {
            throw new UnprocessableEntityHttpException('Анкетата е завршена.');
        }

        $option = PollOption::query()
            ->whereKey($validated['poll_option_id'])
            ->where('poll_id', $poll->id)
            ->first();

        if ($option === null) {
            throw new UnprocessableEntityHttpException('Опцијата не припаѓа на оваа анкета.');
        }

        $user = $request->user();

        $alreadyVoted = PollVote::query()
            ->where('poll_id', $poll->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyVoted) {
            throw new ConflictHttpException('Веќе гласаше на оваа анкета.');
        }

        DB::transaction(function () use ($poll, $option, $user): void {
            PollVote::query()->create([
                'poll_id' => $poll->id,
                'poll_option_id' => $option->id,
                'user_id' => $user->id,
            ]);
        });

        $poll->load(['options' => fn ($q) => $q->withCount('votes')])
            ->loadCount('votes');

        return response()->json([
            'data' => new PollResource($poll),
        ]);
    }
}
