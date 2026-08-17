<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Comment shape for the profile activity list (includes parent thread context).
 *
 * @mixin Comment
 */
class ProfileCommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'parent_id' => $this->parent_id,
            'upvotes' => $this->upvotes,
            'has_voted' => (bool) ($this->has_voted ?? false),
            'created_at' => $this->created_at,
            'edited_at' => $this->edited_at,
            'author' => new UserResource($this->whenLoaded('user')),
            'mentions' => UserMentionResource::collection($this->mentionedUsers())->resolve(),
            'thread' => $this->whenLoaded('thread', fn () => $this->thread === null ? null : [
                'id' => $this->thread->id,
                'title' => $this->thread->title,
                'forum' => $this->thread->relationLoaded('forum') && $this->thread->forum !== null
                    ? [
                        'id' => $this->thread->forum->id,
                        'name' => $this->thread->forum->name,
                        'slug' => $this->thread->forum->slug,
                        'type' => $this->thread->forum->type,
                        'imageUrl' => $this->thread->forum->imageUrl,
                    ]
                    : null,
            ]),
        ];
    }
}
