<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Report;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view reports');

        $activeTab = $request->get('tab', 'queue');

        $reports = Report::query()
            ->with([
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
            ])
            ->when($request->filled('source'), function ($query) use ($request) {
                $query->where('source', $request->source);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('reportable_type', "App\Models\\".$request->type);
            })
            ->when($request->filled('reason'), function ($query) use ($request) {
                $query->where('reason', $request->reason);
            })
            ->where('status', 'pending')
            ->whereHasMorph('reportable', [Comment::class, Thread::class, User::class], function ($query, $type) {
                if (in_array($type, [Comment::class, Thread::class])) {
                    $query->withTrashed();
                }
            })
            ->latest()
            ->paginate(10);

        $resolvedReports = Report::query()
            ->with([
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
            ])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('reportable_type', "App\Models\\".$request->type);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->whereHasMorph('reportable', [Comment::class, Thread::class, User::class], function ($query, $type) {
                if (in_array($type, [Comment::class, Thread::class])) {
                    $query->withTrashed();
                }
            })
            ->latest('updated_at')
            ->paginate(10, ['*'], 'history_page');

        return view('admin.reports.index', compact('reports', 'resolvedReports', 'activeTab'));
    }

    public function approve(Report $report)
    {
        $this->authorize('approve reports');

        $report->update([
            'status' => 'approved',
        ]);

        return back()->with(['success' => 'Пријавата е успешно одобрена!']);
    }

    public function reject(Report $report)
    {
        $this->authorize('reject reports');

        $report->update([
            'status' => 'rejected',
        ]);

        return back()->with(['success' => 'Пријавата е успешно одбиена!']);
    }
}
