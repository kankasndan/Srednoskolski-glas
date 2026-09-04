<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

/**
 * In-app bell payload for the signed-in student. Admin moderation
 * notifications are filtered out before this resource is used.
 *
 * @mixin DatabaseNotification
 */
class UserNotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->data;

        return [
            'id' => $this->id,
            'reason' => $data['reason'] ?? null,
            'title' => $data['title'] ?? 'Известување',
            'message' => $data['message'] ?? '',
            'url' => $this->safeUrl($data['url'] ?? null),
            'actor_username' => $data['actor_username'] ?? null,
            'actor_image_url' => $data['actor_image_url'] ?? null,
            'read_at' => $this->read_at?->toJSON(),
            'created_at' => $this->created_at?->toJSON(),
        ];
    }

    private function safeUrl(mixed $url): string
    {
        if (! is_string($url) || $url === '') {
            return '/feed';
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        return '/feed';
    }
}
