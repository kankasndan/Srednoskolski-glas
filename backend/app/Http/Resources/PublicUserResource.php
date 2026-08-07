<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public profile banner payload (no email / secrets).
 *
 * @mixin \App\Models\User
 */
class PublicUserResource extends JsonResource
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
            'imageUrl' => $this->imageUrl,
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
