<?php

namespace App\Http\Resources;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Forum
 */
class ForumResource extends JsonResource
{
    /**
     * When false, omit description/banner (sidebar card). Default true for full forum pages.
     */
    public bool $withDetails = true;

    public function card(): static
    {
        $this->withDetails = false;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'school_id' => $this->school_id,
            'imageUrl' => $this->imageUrl,
            'threads_count' => $this->threads_count,
            'members_count' => $this->members_count,
            'is_following' => $this->when(
                $this->withDetails && $user !== null,
                fn () => $user->forums()->where('forums.id', $this->id)->exists(),
            ),
            'can_create_thread' => $user !== null
                && $user->can('create', [Thread::class, $this->resource]),
            // Own school forum stays followed from onboarding; others may follow/unfollow.
            'is_own_school_forum' => $this->when(
                $this->withDetails && $user !== null && $this->type === 'school',
                fn () => $user->schoolForumId() === (int) $this->id,
            ),
            'description' => $this->when($this->withDetails, $this->description),
            'bannerUrl' => $this->when($this->withDetails, $this->bannerUrl),
            'school' => $this->when(
                $this->withDetails && $this->relationLoaded('school'),
                function () {
                    if ($this->school === null) {
                        return null;
                    }

                    return [
                        'id' => $this->school->id,
                        'name' => $this->school->name,
                        'city' => $this->school->city?->name,
                    ];
                },
            ),
        ];
    }
}
