<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Authenticated session payload. Includes email and school data for the owner,
 * but not provider ids, role, or other internals.
 *
 * @mixin User
 */
class MeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $studentData = $this->studentData;
        $school = $studentData?->school;
        $city = $school?->city;
        $forum = $school?->forum;
        $vocation = $studentData?->vocation;

        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'imageUrl' => $this->imageUrl,
            'onboarding_completed_at' => $this->onboarding_completed_at,
            'last_active_at' => $this->last_active_at,
            'created_at' => $this->created_at,
            'student_data' => $studentData === null ? null : [
                'grade' => $studentData->grade,
                'school' => $school === null ? null : [
                    'id' => $school->id,
                    'name' => $school->name,
                    'city' => $city === null ? null : [
                        'id' => $city->id,
                        'name' => $city->name,
                    ],
                    'forum' => $forum === null ? null : [
                        'id' => $forum->id,
                        'slug' => $forum->slug,
                        'name' => $forum->name,
                    ],
                ],
                'vocation' => $vocation === null ? null : [
                    'id' => $vocation->id,
                    'name' => $vocation->name,
                ],
            ],
        ];
    }
}
