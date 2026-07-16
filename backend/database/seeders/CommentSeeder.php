<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Comments referencing threads by title and users by email, with nested replies.
     *
     * @var list<array{thread: string, author: string, content: string, replies?: list<array{author: string, content: string}>}>
     */
    private const COMMENTS = [
        [
            'thread' => 'Кога почнуваат пријавите за државна матура?',
            'author' => 'demo@example.com',
            'content' => 'Обично пријавите се отвораат кон крајот на март. Провери на страницата на матурската комисија.',
            'replies' => [
                ['author' => 'ana@example.com', 'content' => 'Благодарам! Ќе проверам таму.'],
                ['author' => 'marko@example.com', 'content' => 'Мене професорката ми кажа дека е во април оваа година.'],
            ],
        ],
        [
            'thread' => 'Кога почнуваат пријавите за државна матура?',
            'author' => 'profesor@example.com',
            'content' => 'Датумите официјално се објавуваат преку училиштата, следете ги известувањата.',
            'replies' => [],
        ],
        [
            'thread' => 'Совети за учење математика за матура',
            'author' => 'stefan@example.com',
            'content' => 'Одлични белешки! Дали имаш и задачи за вежбање со решенија?',
            'replies' => [
                ['author' => 'marko@example.com', 'content' => 'Да, ќе ги качам во следниот пост.'],
            ],
        ],
        [
            'thread' => 'Кои AI алатки ги користите за учење?',
            'author' => 'elena@example.com',
            'content' => 'Јас користам AI за да ми објаснува тешки концепти со едноставни зборови.',
            'replies' => [
                ['author' => 'demo@example.com', 'content' => 'Точно, најдобро е за објаснувања чекор по чекор.'],
                ['author' => 'ivana@example.com', 'content' => 'Внимавајте да не се потпирате целосно, проверувајте ги информациите.'],
            ],
        ],
        [
            'thread' => 'ФИНКИ или ФЕИТ за компјутерски науки?',
            'author' => 'nikola@example.com',
            'content' => 'Зависи што те интересира повеќе — софтвер или хардвер. ФИНКИ е повеќе софтверски ориентиран.',
            'replies' => [
                ['author' => 'stefan@example.com', 'content' => 'Мене ме интересира софтвер, значи ФИНКИ е подобар избор.'],
            ],
        ],
        [
            'thread' => 'Прв програмски јазик за почетници?',
            'author' => 'stefan@example.com',
            'content' => 'Python е одличен за почеток, синтаксата е чиста и лесна за разбирање.',
            'replies' => [
                ['author' => 'demo@example.com', 'content' => 'Се согласувам, и има огромна заедница и материјали.'],
            ],
        ],
        [
            'thread' => 'Како се справувате со стрес пред испити?',
            'author' => 'ivana@example.com',
            'content' => 'Дишни вежби и добар сон ноќта пред испитот ми помагаат најмногу.',
            'replies' => [],
        ],
        [
            'thread' => 'Претставете се тука!',
            'author' => 'ana@example.com',
            'content' => 'Здраво! Јас сум Ана, втора година, здравствена струка од Битола.',
            'replies' => [
                ['author' => 'elena@example.com', 'content' => 'Добредојде Ана!'],
            ],
        ],
        [
            'thread' => 'Стипендии за студии во странство',
            'author' => 'demo@example.com',
            'content' => 'Erasmus+ и DAAD се одлични почетни точки за пребарување.',
            'replies' => [],
        ],
    ];

    public function run(): void
    {
        foreach (self::COMMENTS as $comment) {
            $thread = Thread::where('title', $comment['thread'])->first();
            $author = User::where('email', $comment['author'])->first();

            if ($thread === null || $author === null) {
                continue;
            }

            $parent = Comment::updateOrCreate([
                'thread_id' => $thread->id,
                'user_id' => $author->id,
                'parent_id' => null,
                'content' => $comment['content'],
            ]);

            foreach ($comment['replies'] ?? [] as $reply) {
                $replyAuthor = User::where('email', $reply['author'])->first();

                if ($replyAuthor === null) {
                    continue;
                }

                $parent->replies()->updateOrCreate(
                    ['user_id' => $replyAuthor->id, 'content' => $reply['content']],
                    ['thread_id' => $thread->id],
                );
            }
        }
    }
}
