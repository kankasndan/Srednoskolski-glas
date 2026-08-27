<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Notifications\NewAppealNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppealController extends Controller
{
    private const ALREADY_RESOLVED = 'Жалбата е веќе разгледана.';

    public function index(Request $request)
    {
        $this->authorize('view appeals');

        $tab = $request->get('tab') === 'history' ? 'history' : 'queue';

        $appeals = Appeal::query()
            ->with('sanction', 'user')
            ->where('status', 'pending')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $resolvedAppeals = Appeal::whereIn('status', ['accepted', 'rejected'])
            ->with('sanction', 'user', 'admin')
            ->latest('resolved_at')
            ->paginate(10, ['*'], 'history_page')
            ->withQueryString();

        return view('admin.appeals.index', compact('appeals', 'resolvedAppeals', 'tab'));
    }

    public function show(Appeal $appeal)
    {
        $this->authorize('view appeal details');

        $appeal->load([
            'sanction.issuedBy',
            'sanction.report' => fn ($query) => $query->withTrashed(),
            'sanction.report.reportable',
            'user.studentData.school',
            'admin',
        ]);

        return view('admin.appeals.show', compact('appeal'));
    }

    public function accept(Appeal $appeal)
    {
        $this->authorize('accept appeals');

        if ($appeal->status !== 'pending') {
            return redirect()->route('appeal.index')->withErrors(['status' => self::ALREADY_RESOLVED]);
        }

        DB::transaction(function () use ($appeal): void {
            // Re-read under a lock so two moderators can't both lift the sanction.
            $locked = Appeal::query()->whereKey($appeal->getKey())->lockForUpdate()->first();

            if ($locked === null || $locked->status !== 'pending') {
                return;
            }

            $locked->loadMissing('sanction.report');

            $sanction = $locked->sanction;
            if ($sanction !== null) {
                $sanction->update([
                    'revoked_at' => now(),
                    'revoked_by' => Auth::id(),
                ]);
                $sanction->report?->delete();
                $sanction->delete();
            }

            $locked->update($this->resolution('accepted'));
        });

        if ($appeal->fresh()?->status !== 'accepted') {
            return redirect()->route('appeal.index')->withErrors(['status' => self::ALREADY_RESOLVED]);
        }

        NewAppealNotification::markTargetRead($appeal);

        return redirect()->route('appeal.index', ['tab' => 'queue'])->with(['success' => 'Жалбата е успешно прифатена.']);
    }

    public function reject(Appeal $appeal)
    {
        $this->authorize('reject appeals');

        $updated = Appeal::query()
            ->whereKey($appeal->getKey())
            ->where('status', 'pending')
            ->update($this->resolution('rejected'));

        if ($updated === 0) {
            return redirect()->route('appeal.index')->withErrors(['status' => self::ALREADY_RESOLVED]);
        }

        NewAppealNotification::markTargetRead($appeal);

        return redirect()->route('appeal.index', ['tab' => 'queue'])->with(['success' => 'Жалбата е успешно одбиена.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolution(string $status): array
    {
        return [
            'status' => $status,
            'admin_id' => Auth::id(),
            'resolved_at' => now(),
        ];
    }
}
