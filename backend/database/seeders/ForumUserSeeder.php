<?php

namespace Database\Seeders;

use App\Models\Forum;
use App\Models\User;
use Illuminate\Database\Seeder;

class ForumUserSeeder extends Seeder
{
    /**
     * Forum memberships keyed by user email, listing joined general forum names.
     *
     * @var array<string, list<string>>
     */
    private const MEMBERSHIPS = [
        'demo@example.com' => ['Општи дискусии', 'Вештачка интелегенција', 'Технологија и програмирање', 'Државна матура'],
        'ana@example.com' => ['Државна матура', 'Ментално здравје', 'Помош при учење'],
        'marko@example.com' => ['Помош при учење', 'Факултети', 'Спорт'],
        'elena@example.com' => ['Странски јазици', 'Студии во странство', 'Општи дискусии'],
        'stefan@example.com' => ['Факултети', 'Технологија и програмирање', 'Кариера и професии'],
        'ivana@example.com' => ['Кариера и професии', 'Забава и култура', 'Социјални прашања'],
        'nikola@example.com' => ['Спорт', 'Општи дискусии'],
        'profesor@example.com' => ['Претстави се', 'Слободни дискусии', 'Општи дискусии'],
        'test@example.com' => ['Општи дискусии', 'Државна матура'],
    ];

    public function run(): void
    {
        foreach (self::MEMBERSHIPS as $email => $forumNames) {
            $user = User::where('email', $email)->first();

            if ($user === null) {
                continue;
            }

            $forumIds = Forum::where('type', 'general')
                ->whereIn('name', $forumNames)
                ->pluck('id');

            $user->forums()->syncWithoutDetaching($forumIds);
        }
    }
}
