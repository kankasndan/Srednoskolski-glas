<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public author payload. Never exposes email/password/tokens.
 *
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $school = $this->studentData?->school;

        return [
            'id' => $this->id,
            'username' => $this->username,
            'imageUrl' => $this->imageUrl,
            'school' => $school === null ? null : [
                'id' => $school->id,
                'name' => $school->name,
                'city' => $school->city?->name,
            ],
        ];
    }
}
