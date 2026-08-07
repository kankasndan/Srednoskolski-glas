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
        // Државна матура — at least 15 threads for pagination / API testing
        ['title' => 'Кога почнуваат пријавите за државна матура?', 'description' => 'Дали некој знае точните датуми за пријавување на државна матура оваа година? Не можам да најдам официјална информација.', 'forum' => 'Државна матура', 'author' => 'ana@example.com', 'upvotes' => 42, 'views' => 890, 'anonymous' => false, 'created_days_ago' => 2],
        ['title' => 'Кои предмети се задолжителни на матура?', 'description' => 'Сакам јасна листа: што е задолжително, а што изборно за гимназија vs стручно.', 'forum' => 'Државна матура', 'author' => 'marko@example.com', 'upvotes' => 67, 'views' => 1420, 'anonymous' => false, 'created_days_ago' => 1],
        ['title' => 'Математика напредно или основно ниво?', 'description' => 'Размислувам дали да одам на напредно. Колку е потешко во споредба со основното?', 'forum' => 'Државна матура', 'author' => 'stefan@example.com', 'upvotes' => 55, 'views' => 1100, 'anonymous' => false, 'created_days_ago' => 3],
        ['title' => 'Каде да најдам стари матурски тестови?', 'description' => 'Барам PDF-и од претходни години по македонски, математика и англиски. Линкови?', 'forum' => 'Државна матура', 'author' => 'elena@example.com', 'upvotes' => 91, 'views' => 2200, 'anonymous' => false, 'edited' => true, 'created_days_ago' => 4],
        ['title' => 'Колку поени треба за добар просек?', 'description' => 'Каква е скалата за оценување и колку поени реално треба за 4 или 5?', 'forum' => 'Државна матура', 'author' => 'ivana@example.com', 'upvotes' => 38, 'views' => 780, 'anonymous' => false, 'created_days_ago' => 5],
        ['title' => 'Англиски матура — listening совети', 'description' => 'Listening ми е најслаб дел. Како вежбавте и кои ресурси ви помогнаа?', 'forum' => 'Државна матура', 'author' => 'demo@example.com', 'upvotes' => 44, 'views' => 960, 'anonymous' => false, 'created_days_ago' => 6],
        ['title' => 'Македонски јазик: есеј структура', 'description' => 'Како да структурирам есеј за матура? Примери за вовед, развивање и заклучок.', 'forum' => 'Државна матура', 'author' => 'ana@example.com', 'upvotes' => 73, 'views' => 1680, 'anonymous' => false, 'created_days_ago' => 7],
        ['title' => 'Датуми на испити оваа година', 'description' => 'Дали е објавен конечен распоред за испитните рокови? Споделете ако имате линк од МОН.', 'forum' => 'Државна матура', 'author' => 'nikola@example.com', 'upvotes' => 120, 'views' => 3050, 'anonymous' => false, 'created_days_ago' => 1],
        ['title' => 'Може ли да се полага повторно?', 'description' => 'Ако не сум задоволен од резултатот, дали има втор рок и како се пријавува?', 'forum' => 'Државна матура', 'author' => 'test@example.com', 'upvotes' => 29, 'views' => 540, 'anonymous' => true, 'created_days_ago' => 8],
        ['title' => 'Физика како изборен предмет', 'description' => 'Дали вреди физика како изборна за матура ако сакам ФЕИТ? Колку е тешка?', 'forum' => 'Државна матура', 'author' => 'stefan@example.com', 'upvotes' => 36, 'views' => 710, 'anonymous' => false, 'created_days_ago' => 9],
        ['title' => 'Хемија — формулите што мора да се знаат', 'description' => 'Правам листа на најважни формули и реакции. Додадете што ви се паднало на тест.', 'forum' => 'Државна матура', 'author' => 'marko@example.com', 'upvotes' => 48, 'views' => 890, 'anonymous' => false, 'created_days_ago' => 10],
        ['title' => 'Историја: кои теми се најчести?', 'description' => 'Од кои периоди најчесто има прашања? Антички период, Отомански, XX век…?', 'forum' => 'Државна матура', 'author' => 'ivana@example.com', 'upvotes' => 41, 'views' => 830, 'anonymous' => false, 'created_days_ago' => 11],
        ['title' => 'Како да се организираме 3 месеци пред матура', 'description' => 'Споделете распореди за учење: колку часа дневно, кои предмети прво, паузи итн.', 'forum' => 'Државна матура', 'author' => 'elena@example.com', 'upvotes' => 85, 'views' => 1940, 'anonymous' => false, 'edited' => true, 'created_days_ago' => 12],
        ['title' => 'Потребни документи за пријава', 'description' => 'Што точно се носи за пријавување? Лична карта, уверение, уплатница…?', 'forum' => 'Државна матура', 'author' => 'demo@example.com', 'upvotes' => 52, 'views' => 1200, 'anonymous' => false, 'created_days_ago' => 13],
        ['title' => 'Резултати — кога се објавуваат?', 'description' => 'Колку дена после полагањето обично излегуваат резултатите и каде се гледаат?', 'forum' => 'Државна матура', 'author' => 'ana@example.com', 'upvotes' => 61, 'views' => 1500, 'anonymous' => false, 'created_days_ago' => 14],
        ['title' => 'Онлајн курсеви за матура — вредат ли?', 'description' => 'Има ли добри онлајн курсеви/YouTube канали специјално за државна матура во МК?', 'forum' => 'Државна матура', 'author' => 'nikola@example.com', 'upvotes' => 34, 'views' => 670, 'anonymous' => true, 'created_days_ago' => 15],

        // Other forums
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
