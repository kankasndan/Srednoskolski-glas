<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThreadSeeder extends Seeder
{
    /**
     * Discussion threads referencing forums by slug and authors by email.
     *
     * @var list<array{title: string, description: string, forum: string, author: string, upvotes: int, views: int, anonymous: bool}>
     */
    private const THREADS = [
        ['title' => 'Кога почнуваат пријавите за државна матура?', 'description' => 'Дали некој знае точните датуми за пријавување на државна матура оваа година? Не можам да најдам официјална информација.', 'forum' => 'drzavna_matura', 'author' => 'ana@example.com', 'upvotes' => 42, 'views' => 890, 'anonymous' => false],
        ['title' => 'Совети за учење математика за матура', 'description' => 'Ги споделувам моите белешки и совети како да се подготвите за матурскиот испит по математика.', 'forum' => 'pomos_pri_ucene', 'author' => 'marko@example.com', 'upvotes' => 128, 'views' => 2340, 'anonymous' => false],
        ['title' => 'Кои AI алатки ги користите за учење?', 'description' => 'Ме интересира кои вештачки интелегенции ви помагаат најмногу при подготовка на домашни и проекти.', 'forum' => 'vestacka_intelegencija', 'author' => 'demo@example.com', 'upvotes' => 76, 'views' => 1520, 'anonymous' => false],
        ['title' => 'ФИНКИ или ФЕИТ за компјутерски науки?', 'description' => 'Размислувам помеѓу овие два факултети. Кои се предностите и недостатоците според вашето искуство?', 'forum' => 'fakulteti', 'author' => 'stefan@example.com', 'upvotes' => 95, 'views' => 3100, 'anonymous' => false],
        ['title' => 'Како да го подобрам мојот англиски говор?', 'description' => 'Разбирам добро, но кога треба да зборувам се блокирам. Некои практични совети?', 'forum' => 'stranski_jazici', 'author' => 'elena@example.com', 'upvotes' => 33, 'views' => 640, 'anonymous' => false],
        ['title' => 'Кои професии се барани после средно?', 'description' => 'Дали вреди веднаш да се вработам или да продолжам на факултет? Кои занимања се исплатливи?', 'forum' => 'kariera_i_profesii', 'author' => 'ivana@example.com', 'upvotes' => 51, 'views' => 1180, 'anonymous' => false],
        ['title' => 'Прв програмски јазик за почетници?', 'description' => 'Сакам да почнам да учам програмирање. Со кој јазик препорачувате да започнам?', 'forum' => 'tehnologija_i_programirane', 'author' => 'demo@example.com', 'upvotes' => 64, 'views' => 1975, 'anonymous' => true],
        ['title' => 'Кој спорт го тренирате?', 'description' => 'Ајде да видиме колку сме активни! Јас тренирам кошарка три пати неделно.', 'forum' => 'sport', 'author' => 'nikola@example.com', 'upvotes' => 29, 'views' => 720, 'anonymous' => false],
        ['title' => 'Како се справувате со стрес пред испити?', 'description' => 'Секогаш имам голема анксиозност пред тестови. Како вие се смирувате?', 'forum' => 'mentalno_zdravje', 'author' => 'ana@example.com', 'upvotes' => 112, 'views' => 2610, 'anonymous' => true],
        ['title' => 'Претставете се тука!', 'description' => 'Добредојдовте на форумот! Напишете нешто за себе, вашето училиште и интереси.', 'forum' => 'pretstavi_se', 'author' => 'profesor@example.com', 'upvotes' => 18, 'views' => 430, 'anonymous' => false],
        ['title' => 'Стипендии за студии во странство', 'description' => 'Собирам листа на стипендии за средношколци кои сакаат да студираат надвор. Додадете ги оние што ги знаете.', 'forum' => 'studii_vo_stranstvo', 'author' => 'elena@example.com', 'upvotes' => 87, 'views' => 1890, 'anonymous' => false],
        ['title' => 'Најдобри места за дружење во градот', 'description' => 'Каде излегувате после училиште? Споделете добри локали и активности.', 'forum' => 'zabava_i_kultura', 'author' => 'ivana@example.com', 'upvotes' => 24, 'views' => 560, 'anonymous' => false],
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::THREADS as $thread) {
            $forumId = DB::table('forums')->where('slug', $thread['forum'])->value('id');
            $userId = DB::table('users')->where('email', $thread['author'])->value('id');

            if ($forumId === null || $userId === null) {
                continue;
            }

            DB::table('threads')->updateOrInsert(
                ['title' => $thread['title'], 'forum_id' => $forumId],
                [
                    'description' => $thread['description'],
                    'upvotes' => $thread['upvotes'],
                    'views' => $thread['views'],
                    'user_id' => $userId,
                    'is_anonymous' => $thread['anonymous'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }
}
