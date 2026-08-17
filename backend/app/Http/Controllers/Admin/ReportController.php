<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Report;
use App\Models\Thread;
use App\Models\User;
use App\Notifications\NewReportNotification;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    private const ALREADY_RESOLVED = 'Пријавата е веќе разгледана.';

    public function index(Request $request)
    {
        $this->authorize('view reports');

        $groups = Report::query()
            ->select('reportable_type', 'reportable_id')
            ->selectRaw('MAX(id) as latest_id')
            ->selectRaw('COUNT(*) as reports_count')
            ->when($request->filled('source'), function ($query) use ($request) {
                $query->where('source', $request->source);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('reportable_type', 'App\\Models\\'.$request->type);
            })
            ->when($request->filled('reason'), function ($query) use ($request) {
                $query->where('reason', $request->reason);
            })
            ->where('status', 'pending')
            ->whereHasMorph('reportable', [Comment::class, Thread::class, User::class], function ($query, $type) {
                if (in_array($type, [Comment::class, Thread::class], true)) {
                    $query->withTrashed();
                }
            })
            ->groupBy('reportable_type', 'reportable_id')
            ->orderByDesc('latest_id')
            ->paginate(10)
            ->withQueryString();

        $latestReports = Report::query()
            ->with($this->reportableEagerLoad())
            ->whereIn('id', $groups->pluck('latest_id'))
            ->get()
            ->keyBy('id');

        $siblings = $this->pendingSiblings($groups->getCollection());

        $reports = new LengthAwarePaginator(
            $groups->getCollection()->map(function (object $group) use ($latestReports, $siblings): ?Report {
                $report = $latestReports->get($group->latest_id);

                if ($report === null) {
                    return null;
                }

                $key = $group->reportable_type.'#'.$group->reportable_id;
                $groupReports = $siblings->get($key, collect());

                $report->setAttribute('reports_count', (int) $group->reports_count);
                $report->setAttribute(
                    'group_reporters',
                    $groupReports->pluck('reporter')->filter()->unique('id')->values(),
                );

                return $report;
            })->filter()->values(),
            $groups->total(),
            $groups->perPage(),
            $groups->currentPage(),
            [
                'path' => $groups->path(),
                'query' => $request->query(),
            ],
        );

        $resolvedReports = Report::query()
            ->with($this->reportableEagerLoad())
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('reportable_type', 'App\\Models\\'.$request->type);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->whereHasMorph('reportable', [Comment::class, Thread::class, User::class], function ($query, $type) {
                if (in_array($type, [Comment::class, Thread::class], true)) {
                    $query->withTrashed();
                }
            })
            ->latest('updated_at')
            ->paginate(10, ['*'], 'history_page');

        return view('admin.reports.index', compact('reports', 'resolvedReports'));
    }

    public function approve(Report $report)
    {
        $this->authorize('approve reports');

        if ($this->resolveTarget($report, 'approved') === 0) {
            return back()->withErrors(['status' => self::ALREADY_RESOLVED]);
        }

        return back()->with(['success' => 'Пријавата е успешно одобрена!']);
    }

    public function reject(Report $report)
    {
        $this->authorize('reject reports');

        if ($this->resolveTarget($report, 'rejected') === 0) {
            return back()->withErrors(['status' => self::ALREADY_RESOLVED]);
        }

        return back()->with(['success' => 'Пријавата е успешно одбиена!']);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportableEagerLoad(): array
    {
        return [
            'reporter',
            'reportable' => function ($morphTo) {
                $morphTo->morphWith([
                    Comment::class => ['user', 'thread.forum'],
                    Thread::class => ['user', 'forum'],
                    User::class => ['studentData.school.city'],
                ]);
                $morphTo->constrain([
                    Comment::class => fn ($query) => $query->withTrashed(),
                    Thread::class => fn ($query) => $query->withTrashed(),
                ]);
            },
        ];
    }

    /**
     * @param  Collection<int, object>  $groups
     * @return Collection<string, Collection<int, Report>>
     */
    private function pendingSiblings(Collection $groups): Collection
    {
        if ($groups->isEmpty()) {
            return collect();
        }

        return Report::query()
            ->with('reporter')
            ->where('status', 'pending')
            ->where(function ($query) use ($groups): void {
                foreach ($groups as $group) {
                    $query->orWhere(function ($inner) use ($group): void {
                        $inner->where('reportable_type', $group->reportable_type)
                            ->where('reportable_id', $group->reportable_id);
                    });
                }
            })
            ->get()
            ->groupBy(fn (Report $report) => $report->reportable_type.'#'.$report->reportable_id);
    }

    /**
     * Resolve every pending report on the same target and record who did it.
     * Returns the number of rows touched — zero means someone already handled it.
     */
    private function resolveTarget(Report $report, string $status): int
    {
        $resolved = Report::query()
            ->where('reportable_type', $report->reportable_type)
            ->where('reportable_id', $report->reportable_id)
            ->where('status', 'pending')
            ->update([
                'status' => $status,
                'reviewed_by' => Auth::id(),
            ]);

        if ($resolved > 0) {
            NewReportNotification::markTargetRead($report);
        }

        return $resolved;
    }
}
