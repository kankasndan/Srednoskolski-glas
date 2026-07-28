<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Report;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Sample moderation reports against threads/comments.
     *
     * reason: spam | insulting_content | misinformation | age_inappropriate | other
     * status: pending | approved | rejected
     * source: ai | human
     *
     * @var list<array{
     *     type: 'thread'|'comment',
     *     title?: string,
     *     comment?: string,
     *     reporter: string,
     *     reason: string,
     *     other_reason?: ?string,
     *     status: string,
     *     source: string,
     *     reviewer?: ?string,
     *     ai_confidence?: ?float,
     *     ai_reasoning?: ?string
     * }>
     */
    private const REPORTS = [
        [
            'type' => 'comment',
            'comment' => 'Оваа порака беше премногу груба и е отстранета од модератор.',
            'reporter' => 'ana@example.com',
            'reason' => 'insulting_content',
            'status' => 'approved',
            'source' => 'human',
            'reviewer' => 'moderator@srednoskolskiglas.mk',
        ],
        [
            'type' => 'thread',
            'title' => 'Кои AI алатки ги користите за учење?',
            'reporter' => 'marko@example.com',
            'reason' => 'spam',
            'status' => 'pending',
            'source' => 'human',
        ],
        [
            'type' => 'thread',
            'title' => 'Стипендии за студии во странство',
            'reporter' => 'stefan@example.com',
            'reason' => 'misinformation',
            'status' => 'rejected',
            'source' => 'ai',
            'reviewer' => 'admin@srednoskolskiglas.mk',
            'ai_confidence' => 42.50,
            'ai_reasoning' => 'Low confidence — content looks like a legitimate scholarship list.',
        ],
        [
            'type' => 'comment',
            'comment' => 'Внимавајте да не се потпирате целосно, проверувајте ги информациите.',
            'reporter' => 'demo@example.com',
            'reason' => 'other',
            'other_reason' => 'Мислам дека е оф-топик за темата.',
            'status' => 'pending',
            'source' => 'human',
        ],
    ];

    public function run(): void
    {
        foreach (self::REPORTS as $row) {
            $reporter = User::where('email', $row['reporter'])->first();

            if ($reporter === null) {
                continue;
            }

            $reportable = match ($row['type']) {
                'thread' => Thread::where('title', $row['title'] ?? '')->first(),
                'comment' => Comment::withTrashed()->where('content', $row['comment'] ?? '')->first(),
                default => null,
            };

            if ($reportable === null) {
                continue;
            }

            $reviewerId = null;
            if (! empty($row['reviewer'])) {
                $reviewerId = User::where('email', $row['reviewer'])->value('id');
            }

            $report = Report::updateOrCreate(
                [
                    'reporter_id' => $reporter->id,
                    'reportable_type' => $reportable::class,
                    'reportable_id' => $reportable->id,
                    'reason' => $row['reason'],
                ],
                [
                    'other_reason' => $row['other_reason'] ?? null,
                    'status' => $row['status'],
                    'source' => $row['source'],
                    'reviewed_by' => $reviewerId,
                ],
            );

            // ai_* columns are not mass-assignable on the model.
            if (array_key_exists('ai_confidence', $row) || array_key_exists('ai_reasoning', $row)) {
                $report->ai_confidence = $row['ai_confidence'] ?? null;
                $report->ai_reasoning = $row['ai_reasoning'] ?? null;
                $report->save();
            }
        }
    }
}
