<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = Report::query()
            ->with([
            'reporter',
            'reportable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Comment::class => ['user', 'thread.forum'],
                    \App\Models\Thread::class  => ['user', 'forum'],
                    \App\Models\User::class  => ['studentData.school.city'],
                ]);
            },
        ])
            ->when($request->filled("source"), function ($query) use ($request){
                $query->where("source", $request->source);
            })
            ->when($request->filled("type"), function ($query) use ($request){
                $query->where("reportable_type", "App\Models\\" . $request->type);
            })
            ->when($request->filled("reason"), function ($query) use ($request){
                $query->where("reason", $request->reason);
            })
            ->where('status', 'pending')
            ->whereHasMorph('reportable', [\App\Models\Comment::class, \App\Models\Thread::class, \App\Models\User::class])
            ->latest()
            ->paginate(10);

        return view("admin.reports.index", compact("reports"));
    }

    public function approve(Report $report)
    {
        $report->update([
            "status" => "approved"
        ]);

        return back()->with(['success' => 'Successfully approved report!']);
    }

    public function reject(Report $report)
    {
        $report->update([
            "status" => "rejected"
        ]);

        return back()->with(['success' => 'Successfully rejected report!']);
    }
}
