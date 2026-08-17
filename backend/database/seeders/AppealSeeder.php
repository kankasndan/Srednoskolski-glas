<?php

namespace Database\Seeders;

use App\Models\Appeal;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppealSeeder extends Seeder
{
    /**
     * Appeals against seeded sanctions.
     * status: pending | accepted | rejected
     *
     * @var list<array{
     *     sanction_reason: string,
     *     user: string,
     *     explanation: string,
     *     status: string,
     *     admin?: ?string,
     *     admin_response?: ?string
     * }>
     */
    private const APPEALS = [
        [
            'sanction_reason' => 'Навредувачки коментар на форумот Забава и култура.',
            'user' => 'nikola@example.com',
            'explanation' => 'Коментарот беше напишан во афект. Грешката ја признавам и барам благи мерки наместо забрана.',
            'status' => 'pending',
        ],
        [
            'sanction_reason' => 'Повторено спам однесување во дискусијата за матура.',
            'user' => 'profesor@example.com',
            'explanation' => 'Пратените линкови беа корисни материјали, не спам. Ќе се придржувам до правилата ако има дополнителни ограничувања.',
            'status' => 'accepted',
            'admin' => 'admin@srednoskolskiglas.mk',
            'admin_response' => 'Апелацијата е прифатена. Санкцијата е отстранета и корисникот може повторно да учествува.',
        ],
    ];

    public function run(): void
    {
        foreach (self::APPEALS as $row) {
            $user = User::where('email', $row['user'])->first();
            $sanction = Sanction::withTrashed()->where('reason', $row['sanction_reason'])->first();

            if ($user === null || $sanction === null) {
                continue;
            }

            $adminId = null;
            if (! empty($row['admin'])) {
                $adminId = User::where('email', $row['admin'])->value('id');
            }

            Appeal::updateOrCreate(
                [
                    'sanction_id' => $sanction->id,
                    'user_id' => $user->id,
                ],
                [
                    'explanation' => $row['explanation'],
                    'status' => $row['status'],
                    'admin_id' => $adminId,
                    'resolved_at' => in_array($row['status'], ['accepted', 'rejected'], true) ? now()->subHours(5) : null,
                ],
            );
        }
    }
}
