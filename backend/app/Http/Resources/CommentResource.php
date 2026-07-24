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
            'content' => $this->content,
            'parent_id' => $this->parent_id,
            'upvotes' => $this->upvotes,
            'has_voted' => (bool) ($this->has_voted ?? false),
            'created_at' => $this->created_at,
            'edited_at' => $this->edited_at,
            'deleted_by' => $this->deleted_by,
            'author' => new UserResource($this->whenLoaded('user')),
            'replies' => CommentResource::collection(
                $this->whenLoaded('allReplies'),
            ),
        ];
    }
}
