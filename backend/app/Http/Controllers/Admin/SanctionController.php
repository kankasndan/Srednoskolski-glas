<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Models\Comment;
use App\Models\Report;
use App\Models\Sanction;
use App\Models\Thread;
use App\Models\User;
use App\Support\StaffRoleHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SanctionController extends Controller
{
    public function index()
    {
        $this->authorize('view sanctions');

        $expiredSanctions = Sanction::onlyTrashed()->with('user')->paginate(10);
        $activeSanctions = Sanction::with('user')->whereNull('deleted_at')->paginate(10);
        $permanentBansCount = Sanction::where('type', 'permanent_ban')->count();
        $warnings30Days = Sanction::where('type', 'warning')->where('created_at', '>=', now()->subDays(30))->count();

        $appeals = Appeal::count();

        return view('admin.sanctions.index', compact('expiredSanctions', 'activeSanctions', 'appeals', 'permanentBansCount', 'warnings30Days'));
    }

    public function remove(Sanction $sanction)
    {
        $this->authorize('remove sanctions');

        $sanction->delete();

        return back()->with(['success' => 'Санкцијата е успешно отстранета!']);
    }

    public function store(Request $request)
    {
        $this->authorize('create sanctions');

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:warning,7-day,permanent_ban'],
            'days' => ['required_if:type,custom', 'nullable', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:1000'],
            'report_id' => ['nullable', 'exists:reports,id'],
            'content' => ['nullable', 'boolean'],
        ]);

        $actor = Auth::user();
        abort_unless($actor !== null, 403);

        if ($validated['type'] === 'permanent_ban' && ! $actor->hasAnyRole(['admin', 'super_admin'])) {
            return back()
                ->withErrors(['type' => 'Само админ или супер админ може да издаде трајна забрана.'])
                ->withInput();
        }

        $report = null;
        if (! empty($validated['report_id'])) {
            $report = Report::query()
                ->with(['reportable' => function ($morphTo) {
                    $morphTo->constrain([
                        Comment::class => fn ($query) => $query->withTrashed(),
                        Thread::class => fn ($query) => $query->withTrashed(),
                    ]);
                }])
                ->find($validated['report_id']);

            if ($report) {
                $authorId = $this->resolveReportAuthorId($report);
                if ($authorId === null) {
                    return back()
                        ->withErrors(['user_id' => 'Не може да се одреди авторот на пријавената содржина.'])
                        ->withInput();
                }
                // Never trust the form user_id when sanctioning from a report.
                $validated['user_id'] = $authorId;
            }
        }

        $sanctionedUser = User::findOrFail($validated['user_id']);

        if (StaffRoleHierarchy::isStaff($sanctionedUser->role) || ! $sanctionedUser->hasRole('user')) {
            return back()
                ->withErrors(['user_id' => 'Санкции може да се издадат само на обични корисници.'])
                ->withInput();
        }

        $expiresAt = match ($validated['type']) {
            'warning' => null,
            '7-day' => now()->addDays(7),
            'permanent_ban' => null,
            'custom' => now()->addDays((int) ($validated['days'] ?? 1)),
            default => null,
        };

        Sanction::create([
            'user_id' => $validated['user_id'],
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'expires_at' => $expiresAt,
            'issued_by' => $actor->id,
        ]);

        if ($report) {
            if ($request->boolean('content')) {
                $this->removeReportableContent($report);
            }

            $report->update([
                'status' => 'approved',
            ]);

            return redirect()
                ->route('report.index')
                ->with('success', 'Санкцијата е успешно издадена.');
        }

        if (str_contains(url()->previous(), 'admin/users')) {
            return redirect()
                ->route('user.index')
                ->with('success', 'Санкцијата е успешно издадена.');
        }

        return redirect()
            ->route('sanction.index')
            ->with('success', 'Санкцијата е успешно издадена.');
    }

    private function resolveReportAuthorId(Report $report): ?int
    {
        $reportable = $report->reportable;

        if ($reportable instanceof User) {
            return (int) $reportable->id;
        }

        if ($reportable instanceof Thread || $reportable instanceof Comment) {
            return $reportable->user_id !== null ? (int) $reportable->user_id : null;
        }

        return null;
    }

    /**
     * Soft-delete thread/comment content only — never delete a User account.
     */
    private function removeReportableContent(Report $report): void
    {
        $reportable = $report->reportable;

        if ($reportable instanceof Thread || $reportable instanceof Comment) {
            $reportable->delete();
        }
    }
}
