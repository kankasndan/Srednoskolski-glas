<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            $threadId = DB::table('threads')->where('title', $comment['thread'])->value('id');
            $userId = DB::table('users')->where('email', $comment['author'])->value('id');

            if ($threadId === null || $userId === null) {
                continue;
            }

            $parentId = $this->upsertComment($threadId, $userId, null, $comment['content']);

            foreach ($comment['replies'] ?? [] as $reply) {
                $replyUserId = DB::table('users')->where('email', $reply['author'])->value('id');

                if ($replyUserId === null) {
                    continue;
                }

                $this->upsertComment($threadId, $replyUserId, $parentId, $reply['content']);
            }
        }
    }

    private function upsertComment(int $threadId, int $userId, ?int $parentId, string $content): int
    {
        $now = now();

        DB::table('comments')->updateOrInsert(
            [
                'thread_id' => $threadId,
                'user_id' => $userId,
                'parent_id' => $parentId,
                'content' => $content,
            ],
            ['updated_at' => $now, 'created_at' => $now],
        );

        return (int) DB::table('comments')
            ->where('thread_id', $threadId)
            ->where('user_id', $userId)
            ->where('content', $content)
            ->when(
                $parentId === null,
                fn ($query) => $query->whereNull('parent_id'),
                fn ($query) => $query->where('parent_id', $parentId),
            )
            ->value('id');
    }
}
