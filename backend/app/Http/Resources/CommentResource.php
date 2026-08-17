<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 */
class CommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Hide body on soft-deleted tombstones; structure (replies) stays.
            'content' => $this->trashed() ? '' : $this->content,
            'parent_id' => $this->parent_id,
            'upvotes' => $this->upvotes,
            'has_voted' => (bool) ($this->has_voted ?? false),
            'created_at' => $this->created_at,
            'edited_at' => $this->edited_at,
            'author' => $this->shouldHideAuthor()
                ? null
                : new UserResource($this->whenLoaded('user')),
            'mentions' => $this->trashed()
                ? []
                : UserMentionResource::collection($this->mentionedUsers())->resolve(),
            'replies_count' => (int) ($this->replies_count ?? 0),
        ];
    }

    private function shouldHideAuthor(): bool
    {
        $this->resource->loadMissing('thread');
        $thread = $this->thread;

        if ($thread === null || ! $thread->is_anonymous) {
            return false;
        }

        return (int) $this->user_id === (int) $thread->user_id;
    }
}
