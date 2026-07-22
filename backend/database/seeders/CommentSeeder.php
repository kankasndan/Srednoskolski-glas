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
     * @var list<array{
     *     thread: string,
     *     author: string,
     *     content: string,
     *     upvotes?: int,
     *     edited?: bool,
     *     soft_deleted_by?: string,
     *     replies?: list<array{author: string, content: string, upvotes?: int, edited?: bool}>
     * }>
     */
    private const COMMENTS = [
        [
            'thread' => 'Кога почнуваат пријавите за државна матура?',
            'author' => 'demo@example.com',
            'content' => 'Обично пријавите се отвораат кон крајот на март. Провери на страницата на матурската комисија.',
            'upvotes' => 12,
            'replies' => [
                ['author' => 'ana@example.com', 'content' => 'Благодарам! Ќе проверам таму.', 'upvotes' => 3],
                ['author' => 'marko@example.com', 'content' => 'Мене професорката ми кажа дека е во април оваа година.', 'upvotes' => 5, 'edited' => true],
            ],
        ],
        [
            'thread' => 'Кога почнуваат пријавите за државна матура?',
            'author' => 'profesor@example.com',
            'content' => 'Датумите официјално се објавуваат преку училиштата, следете ги известувањата.',
            'upvotes' => 8,
            'replies' => [],
        ],
        [
            'thread' => 'Совети за учење математика за матура',
            'author' => 'stefan@example.com',
            'content' => 'Одлични белешки! Дали имаш и задачи за вежбање со решенија?',
            'upvotes' => 6,
            'replies' => [
                ['author' => 'marko@example.com', 'content' => 'Да, ќе ги качам во следниот пост.', 'upvotes' => 4],
            ],
        ],
        [
            'thread' => 'Кои AI алатки ги користите за учење?',
            'author' => 'elena@example.com',
            'content' => 'Јас користам AI за да ми објаснува тешки концепти со едноставни зборови.',
            'upvotes' => 9,
            'edited' => true,
            'replies' => [
                ['author' => 'demo@example.com', 'content' => 'Точно, најдобро е за објаснувања чекор по чекор.', 'upvotes' => 2],
                ['author' => 'ivana@example.com', 'content' => 'Внимавајте да не се потпирате целосно, проверувајте ги информациите.', 'upvotes' => 7],
            ],
        ],
        [
            'thread' => 'ФИНКИ или ФЕИТ за компјутерски науки?',
            'author' => 'nikola@example.com',
            'content' => 'Зависи што те интересира повеќе — софтвер или хардвер. ФИНКИ е повеќе софтверски ориентиран.',
            'upvotes' => 15,
            'replies' => [
                ['author' => 'stefan@example.com', 'content' => 'Мене ме интересира софтвер, значи ФИНКИ е подобар избор.', 'upvotes' => 4],
            ],
        ],
        [
            'thread' => 'Прв програмски јазик за почетници?',
            'author' => 'stefan@example.com',
            'content' => 'Python е одличен за почеток, синтаксата е чиста и лесна за разбирање.',
            'upvotes' => 20,
            'replies' => [
                ['author' => 'demo@example.com', 'content' => 'Се согласувам, и има огромна заедница и материјали.', 'upvotes' => 6],
            ],
        ],
        [
            'thread' => 'Како се справувате со стрес пред испити?',
            'author' => 'ivana@example.com',
            'content' => 'Дишни вежби и добар сон ноќта пред испитот ми помагаат најмногу.',
            'upvotes' => 11,
            'replies' => [],
        ],
        [
            'thread' => 'Претставете се тука!',
            'author' => 'ana@example.com',
            'content' => 'Здраво! Јас сум Ана, втора година, здравствена струка од Битола.',
            'upvotes' => 5,
            'replies' => [
                ['author' => 'elena@example.com', 'content' => 'Добредојде Ана!', 'upvotes' => 1],
            ],
        ],
        [
            'thread' => 'Стипендии за студии во странство',
            'author' => 'demo@example.com',
            'content' => 'Erasmus+ и DAAD се одлични почетни точки за пребарување.',
            'upvotes' => 14,
            'replies' => [],
        ],
        [
            // Soft-deleted by moderator — demo tombstone for deleted_by.
            'thread' => 'Најдобри места за дружење во градот',
            'author' => 'nikola@example.com',
            'content' => 'Оваа порака беше премногу груба и е отстранета од модератор.',
            'upvotes' => 0,
            'soft_deleted_by' => 'moderator@srednoskolskiglas.mk',
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

            $parent = Comment::updateOrCreate(
                [
                    'thread_id' => $thread->id,
                    'user_id' => $author->id,
                    'parent_id' => null,
                    'content' => $comment['content'],
                ],
            );

            $parent->upvotes = $comment['upvotes'] ?? 0;
            $parent->edited_at = ($comment['edited'] ?? false) ? now()->subHour() : null;
            $parent->save();

            if ($deleterEmail = $comment['soft_deleted_by'] ?? null) {
                $deleter = User::where('email', $deleterEmail)->first();
                if ($deleter !== null) {
                    $parent->deleted_by = $deleter->id;
                    $parent->deleted_at = now()->subMinutes(30);
                    $parent->save();
                }
            }

            foreach ($comment['replies'] ?? [] as $reply) {
                $replyAuthor = User::where('email', $reply['author'])->first();

                if ($replyAuthor === null) {
                    continue;
                }

                $replyModel = $parent->replies()->updateOrCreate(
                    ['user_id' => $replyAuthor->id, 'content' => $reply['content']],
                    ['thread_id' => $thread->id],
                );

                $replyModel->upvotes = $reply['upvotes'] ?? 0;
                $replyModel->edited_at = ($reply['edited'] ?? false) ? now()->subMinutes(20) : null;
                $replyModel->save();
            }
        }
    }
}
