<?php

namespace Database\Seeders;

use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class ThreadSeeder extends Seeder
{
    /**
     * Discussion threads referencing general forums by name and authors by email.
     *
     * @var list<array{
     *     title: string,
     *     description: string,
     *     forum: string,
     *     author: string,
     *     upvotes: int,
     *     views: int,
     *     anonymous: bool,
     *     edited?: bool,
     *     created_days_ago?: int
     * }>
     */
    private const THREADS = [
        ['title' => 'Кога почнуваат пријавите за државна матура?', 'description' => 'Дали некој знае точните датуми за пријавување на државна матура оваа година? Не можам да најдам официјална информација.', 'forum' => 'Државна матура', 'author' => 'ana@example.com', 'upvotes' => 42, 'views' => 890, 'anonymous' => false, 'created_days_ago' => 2],
        ['title' => 'Совети за учење математика за матура', 'description' => 'Ги споделувам моите белешки и совети како да се подготвите за матурскиот испит по математика.', 'forum' => 'Помош при учење', 'author' => 'marko@example.com', 'upvotes' => 128, 'views' => 2340, 'anonymous' => false, 'edited' => true, 'created_days_ago' => 5],
        ['title' => 'Кои AI алатки ги користите за учење?', 'description' => 'Ме интересира кои вештачки интелегенции ви помагаат најмногу при подготовка на домашни и проекти.', 'forum' => 'Вештачка интелегенција', 'author' => 'demo@example.com', 'upvotes' => 76, 'views' => 1520, 'anonymous' => false, 'created_days_ago' => 1],
        ['title' => 'ФИНКИ или ФЕИТ за компјутерски науки?', 'description' => 'Размислувам помеѓу овие два факултети. Кои се предностите и недостатоците според вашето искуство?', 'forum' => 'Факултети', 'author' => 'stefan@example.com', 'upvotes' => 95, 'views' => 3100, 'anonymous' => false, 'created_days_ago' => 8],
        ['title' => 'Како да го подобрам мојот англиски говор?', 'description' => 'Разбирам добро, но кога треба да зборувам се блокирам. Некои практични совети?', 'forum' => 'Странски јазици', 'author' => 'elena@example.com', 'upvotes' => 33, 'views' => 640, 'anonymous' => false, 'created_days_ago' => 3],
        ['title' => 'Кои професии се барани после средно?', 'description' => 'Дали вреди веднаш да се вработам или да продолжам на факултет? Кои занимања се исплатливи?', 'forum' => 'Кариера и професии', 'author' => 'ivana@example.com', 'upvotes' => 51, 'views' => 1180, 'anonymous' => false, 'created_days_ago' => 4],
        ['title' => 'Прв програмски јазик за почетници?', 'description' => 'Сакам да почнам да учам програмирање. Со кој јазик препорачувате да започнам?', 'forum' => 'Технологија и програмирање', 'author' => 'demo@example.com', 'upvotes' => 64, 'views' => 1975, 'anonymous' => true, 'created_days_ago' => 6],
        ['title' => 'Кој спорт го тренирате?', 'description' => 'Ајде да видиме колку сме активни! Јас тренирам кошарка три пати неделно.', 'forum' => 'Спорт', 'author' => 'nikola@example.com', 'upvotes' => 29, 'views' => 720, 'anonymous' => false, 'created_days_ago' => 1],
        ['title' => 'Како се справувате со стрес пред испити?', 'description' => 'Секогаш имам голема анксиозност пред тестови. Како вие се смирувате?', 'forum' => 'Ментално здравје', 'author' => 'ana@example.com', 'upvotes' => 112, 'views' => 2610, 'anonymous' => true, 'edited' => true, 'created_days_ago' => 7],
        ['title' => 'Претставете се тука!', 'description' => 'Добредојдовте на форумот! Напишете нешто за себе, вашето училиште и интереси.', 'forum' => 'Претстави се', 'author' => 'profesor@example.com', 'upvotes' => 18, 'views' => 430, 'anonymous' => false, 'created_days_ago' => 14],
        ['title' => 'Стипендии за студии во странство', 'description' => 'Собирам листа на стипендии за средношколци кои сакаат да студираат надвор. Додадете ги оние што ги знаете.', 'forum' => 'Студии во странство', 'author' => 'elena@example.com', 'upvotes' => 87, 'views' => 1890, 'anonymous' => false, 'created_days_ago' => 10],
        ['title' => 'Најдобри места за дружење во градот', 'description' => 'Каде излегувате после училиште? Споделете добри локали и активности.', 'forum' => 'Забава и култура', 'author' => 'ivana@example.com', 'upvotes' => 24, 'views' => 560, 'anonymous' => false, 'created_days_ago' => 2],
    ];

    public function run(): void
    {
        foreach (self::THREADS as $thread) {
            $forum = Forum::where('type', 'general')->where('name', $thread['forum'])->first();
            $author = User::where('email', $thread['author'])->first();

            if ($forum === null || $author === null) {
                continue;
            }

            $createdAt = now()->subDays($thread['created_days_ago'] ?? 0);

            $model = Thread::updateOrCreate(
                ['title' => $thread['title'], 'forum_id' => $forum->id],
                [
                    'description' => $thread['description'],
                    'upvotes' => $thread['upvotes'],
                    'views' => $thread['views'],
                    'user_id' => $author->id,
                    'is_anonymous' => $thread['anonymous'],
                ],
            );

            $model->created_at = $createdAt;
            $model->updated_at = $createdAt;
            $model->edited_at = ($thread['edited'] ?? false) ? $createdAt->copy()->addHours(3) : null;
            $model->save();
        }
    }
}
