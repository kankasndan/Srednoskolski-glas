<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use Illuminate\Http\Request;

class AppealController extends Controller
{
    public function index(Request $request)
    {
        $appeals = Appeal::query()
            ->with('sanction', 'user')
            ->where('status', 'pending')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $resolvedAppeals = Appeal::whereIn('status', ['accepted', 'rejected'])
            ->with('sanction', 'user')
            ->paginate(10)
            ->withQueryString();

        return view('admin.appeals.index', compact('appeals', 'resolvedAppeals'));
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
        $appeal->with('sanction.report');

        $appeal->sanction->report->delete();

        $appeal->sanction->delete();

        $appeal->update([
            'status' => 'accepted',
        ]);

        return redirect()->route('appeal.index')->with(['success' => 'Successfully appeal accepted']);
    }

    public function reject(Appeal $appeal)
    {
        $appeal->update([
            'status' => 'rejected',
        ]);

        return redirect()->route('appeal.index')->with(['success' => 'Successfully appeal rejected.']);
    }
}
