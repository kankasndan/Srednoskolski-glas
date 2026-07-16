<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;

trait PresentsAuthor
{
    /**
     * Shape a user into the public author payload returned by the API.
     *
     * @return array<string, mixed>|null
     */
    private function presentAuthor(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $school = $user->studentData?->school;

        return [
            'id' => $user->id,
            'username' => $user->username,
            'imageUrl' => $user->imageUrl,
            'school' => $school === null ? null : [
                'id' => $school->id,
                'name' => $school->name,
                'city' => $school->city?->name,
            ],
        ];
    }
}
