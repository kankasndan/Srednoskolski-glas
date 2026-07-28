<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Mention;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class MentionSeeder extends Seeder
{
    /**
     * Sample @mentions in threads and comments.
     *
     * @var list<array{
     *     type: 'thread'|'comment',
     *     title?: string,
     *     comment?: string,
     *     mentioning: string,
     *     mentioned: string
     * }>
     */
    private const MENTIONS = [
        [
            'type' => 'thread',
            'title' => 'ФИНКИ или ФЕИТ за компјутерски науки?',
            'mentioning' => 'stefan@example.com',
            'mentioned' => 'demo@example.com',
        ],
        [
            'type' => 'comment',
            'comment' => 'Обично пријавите се отвораат кон крајот на март. Провери на страницата на матурската комисија.',
            'mentioning' => 'demo@example.com',
            'mentioned' => 'ana@example.com',
        ],
        [
            'type' => 'comment',
            'comment' => 'Python е одличен за почеток, синтаксата е чиста и лесна за разбирање.',
            'mentioning' => 'stefan@example.com',
            'mentioned' => 'demo@example.com',
        ],
        [
            'type' => 'thread',
            'title' => 'Стипендии за студии во странство',
            'mentioning' => 'elena@example.com',
            'mentioned' => 'ivana@example.com',
        ],
    ];

    public function run(): void
    {
        foreach (self::MENTIONS as $row) {
            $mentioning = User::where('email', $row['mentioning'])->first();
            $mentioned = User::where('email', $row['mentioned'])->first();

            if ($mentioning === null || $mentioned === null) {
                continue;
            }

            $mentionable = match ($row['type']) {
                'thread' => Thread::where('title', $row['title'] ?? '')->first(),
                'comment' => Comment::where('content', $row['comment'] ?? '')->first(),
                default => null,
            };

            if ($mentionable === null) {
                continue;
            }

            Mention::updateOrCreate(
                [
                    'mentionable_type' => $mentionable::class,
                    'mentionable_id' => $mentionable->id,
                    'mentioning_user_id' => $mentioning->id,
                    'mentioned_user_id' => $mentioned->id,
                ],
            );
        }
    }
}
