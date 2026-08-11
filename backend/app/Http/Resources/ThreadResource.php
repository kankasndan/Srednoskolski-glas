<?php

namespace App\Http\Resources;

use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Thread
 */
class ThreadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user('web') ?? $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'upvotes' => $this->upvotes,
            'has_voted' => (bool) ($this->has_voted ?? false),
            // Present only for authenticated viewers (visual follow — MVP spec 6.6).
            'is_following' => $this->when(
                $viewer !== null,
                fn () => (bool) ($this->is_following ?? false),
            ),
            // True when the session user created this thread (works for anonymous posts too).
            'is_owner' => $viewer !== null && (int) $viewer->id === (int) $this->user_id,
            'views' => $this->views,
            'is_anonymous' => $this->is_anonymous,
            'comments_count' => $this->whenCounted('comments'),
            'created_at' => $this->created_at,
            'edited_at' => $this->edited_at,
            'forum' => $this->whenLoaded('forum', fn () => $this->forum === null ? null : [
                'id' => $this->forum->id,
                'name' => $this->forum->name,
                'slug' => $this->forum->slug,
                'type' => $this->forum->type,
                'imageUrl' => $this->forum->imageUrl,
            ]),
            'author' => $this->is_anonymous
                ? null
                : new UserResource($this->whenLoaded('user')),
            'attachments' => ThreadAttachmentResource::collection(
                $this->whenLoaded('threadAttachment'),
            ),
            'poll' => $this->when(
                $this->relationLoaded('poll'),
                fn () => $this->poll === null ? null : new PollResource($this->poll),
            ),
        ];
    }
}
