<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use Illuminate\Http\Request;

class AppealController extends Controller
{
    public function index(Request $request)
    {
        $activeAppealsQuery = Appeal::query()
            ->with('sanction', 'user')
            ->when($request->filled('status') && $request->status !== 'all', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ;

        $activeAppealsTotal = (clone $activeAppealsQuery)->count();
        $appeals = $activeAppealsQuery->paginate(10)->withQueryString();

        $resolvedAppealsQuery = Appeal::query()
            ->onlyTrashed()
            ->with('sanction', 'user')
            ;

        if ($request->filled('history_status') && $request->history_status !== 'all') {
            $resolvedAppealsQuery->where('status', $request->history_status);
        }

        if ($request->filled('range') && $request->range !== 'all') {
            $resolvedAppealsQuery->where('deleted_at', '>=', now()->subDays((int) $request->range));
        }

        $resolvedAppealsTotal = (clone $resolvedAppealsQuery)->count();
        $resolvedAppeals = $resolvedAppealsQuery
            ->latest('deleted_at')
            ->paginate(10, ['*'], 'history_page')
            ->withQueryString();

        return view('admin.appeals.index', compact('appeals', 'resolvedAppeals', 'activeAppealsTotal', 'resolvedAppealsTotal'));
    }

    public function show(Appeal $appeal)
    {
        $appeal->load([
            'sanction.issuedBy',
            'sanction.report.reportable',
            'user.studentData.school',
            'admin',
        ]);

        return view('admin.appeals.show', compact('appeal'));
    }

    public function accept(Appeal $appeal)
    {
        $appeal->with("sanction.report");

        $appeal->sanction->report->delete();
        
        $appeal->sanction->delete();

        $appeal->delete();

        return redirect()->route("appeal.index")->with(['success' => "Successfully appeal accepted"]);
    }

    public function reject(Appeal $appeal)
    {
        
    }
}
