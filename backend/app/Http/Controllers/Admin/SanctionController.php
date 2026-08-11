<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Models\Report;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SanctionController extends Controller
{
    public function index()
    {
        $expiredSanctions = Sanction::onlyTrashed()->with('user')->paginate(10);
        $activeSanctions = Sanction::with('user')->whereNull('deleted_at')->paginate(10);
        $permanentBansCount = Sanction::where('type', 'permanent_ban')->count();
        $warnings30Days = Sanction::where('type', 'warning')->where('created_at', '>=', now()->subDays(30))->count();

        $appeals = Appeal::count();

        return view('admin.sanctions.index', compact('expiredSanctions', 'activeSanctions', 'appeals', 'permanentBansCount', 'warnings30Days'));
    }

    public function remove(Sanction $sanction)
    {
        $sanction->delete();

        return back()->with(['succes' => 'Sanction removed successfully!']);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id',],
            'type' => ['required', 'in:warning,7-day,permanent_ban'],
            'days' => ['required_if:type,custom', 'nullable', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $expiresAt = match ($validated['type']) {
            'warning' => null,
            '7-day' => now()->addDays(7),
            'permanent_ban' => null,
            'custom' => now()->addDays((int) $validated['days']),
            default => null,
        };

        $sanctionedUser = User::findOrFail($validated['user_id']);

        if (! $sanctionedUser->hasRole('user')) {
            // You can abort or return a validation-like error
            return back()
                ->withErrors(['user_id' => 'Sanctions can only be applied to users with the user role.'])
                ->withInput();
        }

        Sanction::create([
            'user_id' => $validated['user_id'],
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'expires_at' => $expiresAt,
            'issued_by' => Auth::id(),
        ]);

        if (str_contains(url()->previous(), 'admin/reports')) {

            $report = Report::find($request->report_id);

            if ($request->boolean('content') && ($report->reportable_type == "App\Models\Comment" || $report->reportable_type == "App\Models\Thread")) {
                $report->reportable->delete();
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
}
