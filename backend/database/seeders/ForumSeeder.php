<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ForumSeeder extends Seeder
{
    /**
     * Topic forums. Slugs mirror the frontend `forums.js` list.
     *
     * @var list<array{name: string, slug: string}>
     */
    private const FORUMS = [
        ['name' => 'Општи дискусии', 'slug' => 'opsti_diskusii'],
        ['name' => 'Државна матура', 'slug' => 'drzavna_matura'],
        ['name' => 'Помош при учење', 'slug' => 'pomos_pri_ucene'],
        ['name' => 'Вештачка интелегенција', 'slug' => 'vestacka_intelegencija'],
        ['name' => 'Факултети', 'slug' => 'fakulteti'],
        ['name' => 'Странски јазици', 'slug' => 'stranski_jazici'],
        ['name' => 'Кариера и професии', 'slug' => 'kariera_i_profesii'],
        ['name' => 'Студии во странство', 'slug' => 'studii_vo_stranstvo'],
        ['name' => 'Ментално здравје', 'slug' => 'mentalno_zdravje'],
        ['name' => 'Воннаставни активности', 'slug' => 'vonnastavni_aktivnosti'],
        ['name' => 'Технологија и програмирање', 'slug' => 'tehnologija_i_programirane'],
        ['name' => 'Забава и култура', 'slug' => 'zabava_i_kultura'],
        ['name' => 'Спорт', 'slug' => 'sport'],
        ['name' => 'Социјални прашања', 'slug' => 'socijalni_prasana'],
        ['name' => 'Претстави се', 'slug' => 'pretstavi_se'],
        ['name' => 'Слободни дискусии', 'slug' => 'slobodni_diskusii'],
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::FORUMS as $forum) {
            DB::table('forums')->updateOrInsert(
                ['slug' => $forum['slug']],
                [
                    'name' => $forum['name'],
                    'description' => 'Форум за темата „'.$forum['name'].'“.',
                    'type' => 'general',
                    'imageUrl' => '/icons/'.$forum['slug'].'.svg',
                    'bannerUrl' => 'https://picsum.photos/seed/'.$forum['slug'].'/1200/300',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }
}
