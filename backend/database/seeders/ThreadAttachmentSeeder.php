<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThreadAttachmentSeeder extends Seeder
{
    /**
     * Attachments referencing threads by title.
     *
     * @var list<array{thread: string, slug: string, url: string, file_id: ?string}>
     */
    private const ATTACHMENTS = [
        ['thread' => 'Кои AI алатки ги користите за учење?', 'slug' => 'image', 'url' => 'https://picsum.photos/seed/ai_tools/800/600', 'file_id' => 'file_ai_tools_001'],
        ['thread' => 'Совети за учење математика за матура', 'slug' => 'file', 'url' => 'https://cdn.example.com/files/matematika_beleski.pdf', 'file_id' => 'file_math_notes_002'],
        ['thread' => 'Прв програмски јазик за почетници?', 'slug' => 'link', 'url' => 'https://roadmap.sh/', 'file_id' => null],
        ['thread' => 'Кој спорт го тренирате?', 'slug' => 'image', 'url' => 'https://picsum.photos/seed/basketball/800/600', 'file_id' => 'file_sport_003'],
        ['thread' => 'ФИНКИ или ФЕИТ за компјутерски науки?', 'slug' => 'link', 'url' => 'https://www.finki.ukim.mk/', 'file_id' => null],
        ['thread' => 'Стипендии за студии во странство', 'slug' => 'file', 'url' => 'https://cdn.example.com/files/stipendii_2026.pdf', 'file_id' => 'file_scholarships_004'],
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::ATTACHMENTS as $attachment) {
            $threadId = DB::table('threads')->where('title', $attachment['thread'])->value('id');

            if ($threadId === null) {
                continue;
            }

            DB::table('thread_attachments')->updateOrInsert(
                ['thread_id' => $threadId, 'url' => $attachment['url']],
                [
                    'slug' => $attachment['slug'],
                    'provider' => 'imagekit',
                    'file_id' => $attachment['file_id'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }
}
