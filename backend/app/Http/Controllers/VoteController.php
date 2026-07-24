<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Thread;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    /**
     * Toggle upvote on a thread.
     *
     * POST /api/threads/{thread}/upvote
     */
    public function toggleThread(Request $request, Thread $thread): JsonResponse
    {
        return $this->toggle($request, $thread);
    }

    /**
     * Toggle upvote on a comment.
     *
     * POST /api/comments/{comment}/upvote
     */
    public function toggleComment(Request $request, Comment $comment): JsonResponse
    {
        return $this->toggle($request, $comment);
    }

    /**
     * Create or remove the current user's vote and sync the denormalized upvotes counter.
     *
     * @param  Thread|Comment  $votable
     */
    private function toggle(Request $request, Model $votable): JsonResponse
    {
        $user = $request->user();

        /** @var array{upvotes: int, has_voted: bool} $result */
        $result = DB::transaction(function () use ($user, $votable): array {
            $existing = Vote::query()
                ->where('user_id', $user->id)
                ->where('votable_type', $votable->getMorphClass())
                ->where('votable_id', $votable->getKey())
                ->first();

            if ($existing !== null) {
                $existing->delete();

                if ($votable->upvotes > 0) {
                    $votable->decrement('upvotes');
                }

                $votable->refresh();

                return [
                    'upvotes' => (int) $votable->upvotes,
                    'has_voted' => false,
                ];
            }

            Vote::query()->create([
                'user_id' => $user->id,
                'votable_type' => $votable->getMorphClass(),
                'votable_id' => $votable->getKey(),
            ]);

            $votable->increment('upvotes');
            $votable->refresh();

            return [
                'upvotes' => (int) $votable->upvotes,
                'has_voted' => true,
            ];
        });

        return response()->json([
            'data' => $result,
        ]);
    }
}
