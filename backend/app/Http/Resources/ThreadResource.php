<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Thread
 */
class ThreadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'upvotes' => $this->upvotes,
            'views' => $this->views,
            'is_anonymous' => $this->is_anonymous,
            'comments_count' => $this->whenCounted('comments'),
            'created_at' => $this->created_at,
            'edited_at' => $this->edited_at,
            'forum' => $this->whenLoaded('forum', fn () => $this->forum === null ? null : [
                'id' => $this->forum->id,
                'name' => $this->forum->name,
                'slug' => $this->forum->slug,
                'imageUrl' => $this->forum->imageUrl,
            ]),
            'author' => $this->is_anonymous
                ? null
                : new UserResource($this->whenLoaded('user')),
            'attachments' => ThreadAttachmentResource::collection(
                $this->whenLoaded('threadAttachment'),
            ),
        ];
    }
}
