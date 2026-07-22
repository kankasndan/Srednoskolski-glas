<?php

namespace Database\Seeders;

use App\Models\Thread;
use App\Models\ThreadAttachment;
use Illuminate\Database\Seeder;

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
        foreach (self::ATTACHMENTS as $attachment) {
            $thread = Thread::where('title', $attachment['thread'])->first();

            if ($thread === null) {
                continue;
            }

            ThreadAttachment::updateOrCreate(
                ['thread_id' => $thread->id, 'url' => $attachment['url']],
                [
                    'slug' => $attachment['slug'],
                    'provider' => 'imagekit',
                    'file_id' => $attachment['file_id'],
                ],
            );
        }
    }
}
