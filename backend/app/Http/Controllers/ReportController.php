<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\FeedHide;
use App\Models\Report;
use App\Models\Thread;
use App\Notifications\NewReportNotification;
use App\Services\Feed\FeedCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    private const REASON_MAP = [
        'Спам' => 'spam',
        'spam' => 'spam',
        'Навредлива содржина' => 'insulting_content',
        'insulting_content' => 'insulting_content',
        'Дезинформација' => 'misinformation',
        'misinformation' => 'misinformation',
        'Несоодветна содржина' => 'age_inappropriate',
        'age_inappropriate' => 'age_inappropriate',
        'Друго' => 'other',
        'other' => 'other',
    ];

    /**
     * Report a thread. Also hides it from the reporter's feed.
     *
     * POST /api/threads/{thread}/report
     */
    public function storeThread(Request $request, Thread $thread): JsonResponse
    {
        return $this->storeReport($request, $thread, hideThreadId: $thread->id);
    }

    /**
     * Report a comment.
     *
     * POST /api/comments/{comment}/report
     */
    public function storeComment(Request $request, Comment $comment): JsonResponse
    {
        return $this->storeReport($request, $comment, hideThreadId: null);
    }

    private function storeReport(Request $request, Thread|Comment $reportable, ?int $hideThreadId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string'],
            'details' => ['nullable', 'string', 'max:2000'],
            'other_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $reasonKey = self::REASON_MAP[$validated['reason']] ?? null;

        if ($reasonKey === null) {
            return response()->json([
                'message' => 'Невалидна причина за пријава.',
                'errors' => [
                    'reason' => ['Избери валидна причина.'],
                ],
            ], 422);
        }

        $otherReason = $validated['other_reason'] ?? null;
        $details = isset($validated['details']) ? trim((string) $validated['details']) : '';

        if ($reasonKey === 'other') {
            $otherReason = $otherReason ?: ($details !== '' ? $details : null);
            if ($otherReason === null || trim($otherReason) === '') {
                return response()->json([
                    'message' => 'За „Друго“ внеси детали.',
                    'errors' => [
                        'details' => ['Внеси дополнителни детали.'],
                    ],
                ], 422);
            }
        } elseif ($details !== '') {
            $otherReason = $otherReason ?: $details;
        }

        $alreadyReported = Report::query()
            ->where('reporter_id', $request->user()->id)
            ->where('reportable_id', $reportable->id)
            ->where('reportable_type', $reportable::class)
            ->exists();

        if ($alreadyReported) {
            throw ValidationException::withMessages([
                'reason' => ['Веќе ја пријави оваа содржина.'],
            ]);
        }

        $report = Report::query()->create([
            'reporter_id' => $request->user()->id,
            'reportable_id' => $reportable->id,
            'reportable_type' => $reportable::class,
            'reason' => $reasonKey,
            'other_reason' => $otherReason,
            'status' => 'pending',
            'source' => 'human',
        ]);

        if ($hideThreadId !== null) {
            FeedHide::query()->firstOrCreate([
                'user_id' => $request->user()->id,
                'thread_id' => $hideThreadId,
            ]);
            FeedCache::forgetForUser($request->user());
        }

        NewReportNotification::syncForReport($report);

        return response()->json([
            'data' => [
                'id' => $report->id,
                'status' => $report->status,
                'reason' => $report->reason,
            ],
        ], 201);
    }
}
