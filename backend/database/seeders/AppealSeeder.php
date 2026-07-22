<?php

namespace Database\Seeders;

use App\Models\Appeal;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppealSeeder extends Seeder
{
    /**
     * Sample appeals against sanctions.
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
            'sanction_reason' => 'Повторено спам однесување (тест санкција).',
            'user' => 'profesor@example.com',
            'explanation' => 'Ова беше тест налог, не сум спамер. Ве молам отстранете ја забраната.',
            'status' => 'pending',
        ],
        [
            'sanction_reason' => 'Навредувачки коментар на форумот Забава и култура.',
            'user' => 'nikola@example.com',
            'explanation' => 'Се извинувам, немав намера да навредам. Ќе внимавам во иднина.',
            'status' => 'accepted',
            'admin' => 'admin@srednoskolskiglas.mk',
            'admin_response' => 'Предупредувањето останува во историјата, но нема дополнителни мерки.',
        ],
    ];

    public function run(): void
    {
        foreach (self::APPEALS as $row) {
            $user = User::where('email', $row['user'])->first();
            $sanction = Sanction::where('reason', $row['sanction_reason'])->first();

            if ($user === null || $sanction === null) {
                continue;
            }

            $adminId = null;
            if (! empty($row['admin'])) {
                $adminId = User::where('email', $row['admin'])->value('id');
            }

            $resolved = in_array($row['status'], ['accepted', 'rejected'], true);

            Appeal::updateOrCreate(
                [
                    'sanction_id' => $sanction->id,
                    'user_id' => $user->id,
                ],
                [
                    'explanation' => $row['explanation'],
                    'status' => $row['status'],
                    'admin_id' => $adminId,
                    'admin_response' => $row['admin_response'] ?? null,
                    'resolved_at' => $resolved ? now()->subHours(5) : null,
                ],
            );
        }
    }
}
