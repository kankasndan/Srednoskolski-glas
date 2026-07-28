<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Database\Seeder;

class SanctionSeeder extends Seeder
{
    /**
     * Sample sanctions. type: warning | temporary_ban | permanent_ban
     *
     * @var list<array{
     *     user: string,
     *     issued_by: string,
     *     type: string,
     *     reason: string,
     *     report_reason?: ?string,
     *     expires_days?: ?int,
     *     acknowledged?: bool,
     *     revoked?: bool,
     *     revoked_by?: ?string
     * }>
     */
    private const SANCTIONS = [
        [
            'user' => 'nikola@example.com',
            'issued_by' => 'moderator@srednoskolskiglas.mk',
            'type' => 'warning',
            'reason' => 'Навредувачки коментар на форумот Забава и култура.',
            'report_reason' => 'insulting_content',
            'acknowledged' => true,
        ],
        [
            'user' => 'profesor@example.com',
            'issued_by' => 'admin@srednoskolskiglas.mk',
            'type' => 'temporary_ban',
            'reason' => 'Повторено спам однесување (тест санкција).',
            'expires_days' => 7,
            'acknowledged' => false,
        ],
        [
            'user' => 'nikola@example.com',
            'issued_by' => 'admin@srednoskolskiglas.mk',
            'type' => 'temporary_ban',
            'reason' => 'Санкција што е повлечена — пример за revoked_at.',
            'expires_days' => 3,
            'revoked' => true,
            'revoked_by' => 'admin@srednoskolskiglas.mk',
        ],
    ];

    public function run(): void
    {
        foreach (self::SANCTIONS as $row) {
            $user = User::where('email', $row['user'])->first();
            $issuer = User::where('email', $row['issued_by'])->first();

            if ($user === null || $issuer === null) {
                continue;
            }

            $reportId = null;
            if (! empty($row['report_reason'])) {
                $reportId = Report::query()
                    ->where('reason', $row['report_reason'])
                    ->where('status', 'approved')
                    ->value('id');
            }

            $revokedBy = null;
            if (! empty($row['revoked_by'])) {
                $revokedBy = User::where('email', $row['revoked_by'])->value('id');
            }

            Sanction::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $row['type'],
                    'reason' => $row['reason'],
                ],
                [
                    'issued_by' => $issuer->id,
                    'report_id' => $reportId,
                    'expires_at' => isset($row['expires_days'])
                        ? now()->addDays($row['expires_days'])
                        : null,
                    'acknowledged_at' => ($row['acknowledged'] ?? false) ? now()->subDay() : null,
                    'revoked_at' => ($row['revoked'] ?? false) ? now()->subHours(2) : null,
                    'revoked_by' => $revokedBy,
                ],
            );
        }
    }
}
