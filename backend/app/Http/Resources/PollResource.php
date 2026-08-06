<?php

namespace App\Http\Resources;

use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Poll
 */
class PollResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Prefer withCount('votes'); never use a filtered votes relation for the total.
        $totalVotes = isset($this->votes_count)
            ? (int) $this->votes_count
            : (int) $this->votes()->count();
        $user = $request->user('web') ?? $request->user();
        $userVoteOptionId = null;

        if ($user !== null) {
            if ($this->relationLoaded('votes')) {
                $userVoteOptionId = $this->votes
                    ->firstWhere('user_id', $user->id)
                    ?->poll_option_id;
            } else {
                $userVoteOptionId = $this->votes()
                    ->where('user_id', $user->id)
                    ->value('poll_option_id');
            }
        }

        return [
            'id' => $this->id,
            'question' => $this->question,
            'ends_at' => $this->ends_at,
            'has_ended' => $this->hasEnded(),
            'total_votes' => $totalVotes,
            'user_voted_option_id' => $userVoteOptionId,
            'options' => $this->whenLoaded('options', function () use ($totalVotes) {
                return $this->options->map(function ($option) use ($totalVotes) {
                    $votes = (int) ($option->votes_count ?? $option->votes()->count());

                    return [
                        'id' => $option->id,
                        'label' => $option->label,
                        'position' => $option->position,
                        'votes_count' => $votes,
                        'percentage' => $totalVotes > 0
                            ? (int) round(($votes / $totalVotes) * 100)
                            : 0,
                    ];
                })->values();
            }),
        ];
    }
}
