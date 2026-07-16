<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ForumUserSeeder extends Seeder
{
    /**
     * Forum memberships keyed by user email, listing joined forum slugs.
     *
     * @var array<string, list<string>>
     */
    private const MEMBERSHIPS = [
        'demo@example.com' => ['opsti_diskusii', 'vestacka_intelegencija', 'tehnologija_i_programirane', 'drzavna_matura'],
        'ana@example.com' => ['drzavna_matura', 'mentalno_zdravje', 'pomos_pri_ucene'],
        'marko@example.com' => ['pomos_pri_ucene', 'fakulteti', 'sport'],
        'elena@example.com' => ['stranski_jazici', 'studii_vo_stranstvo', 'opsti_diskusii'],
        'stefan@example.com' => ['fakulteti', 'tehnologija_i_programirane', 'kariera_i_profesii'],
        'ivana@example.com' => ['kariera_i_profesii', 'zabava_i_kultura', 'socijalni_prasana'],
        'nikola@example.com' => ['sport', 'opsti_diskusii'],
        'profesor@example.com' => ['pretstavi_se', 'slobodni_diskusii', 'opsti_diskusii'],
        'test@example.com' => ['opsti_diskusii', 'drzavna_matura'],
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::MEMBERSHIPS as $email => $slugs) {
            $userId = DB::table('users')->where('email', $email)->value('id');

            if ($userId === null) {
                continue;
            }

            foreach ($slugs as $slug) {
                $forumId = DB::table('forums')->where('slug', $slug)->value('id');

                if ($forumId === null) {
                    continue;
                }

                DB::table('forum_user')->updateOrInsert(
                    ['user_id' => $userId, 'forum_id' => $forumId],
                    ['updated_at' => $now, 'created_at' => $now],
                );
            }
        }
    }
}
