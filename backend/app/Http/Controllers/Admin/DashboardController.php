<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Forum;
use App\Models\StudentData;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view dashboard');

        $range = $this->resolveRange($request);

        $totalUsers = User::count();

        $activeUsers = User::where('last_active_at', '>=', now()->subDays(7))->count();

        $newRegistrations30d = User::where('created_at', '>=', now()->subDays(30))->count();

        $usersByCity = City::whereHas('studentData')->withCount('studentData')->get();

        $usersBySchool = StudentData::with('school')->selectRaw('school_id, count(*) as total')
            ->groupBy('school_id')
            ->get();

        $topForums = Forum::withCount('threads')
            ->orderByDesc('threads_count')->limit(5)->get();

        $registrationLabels = $this->registrationLabels($range);
        $registrationCounts = $this->registrationCounts($range);
        //

        return view('admin.dashboard.index', compact('totalUsers', 'activeUsers', 'newRegistrations30d', 'usersBySchool', 'usersByCity', 'topForums', 'registrationLabels', 'registrationCounts'));
    }

    /**
     * The range drives day-by-day loops and the export cache key, so it has to be
     * a bounded integer — `?range=999999999` would otherwise build an array that
     * size and hand every value its own cache entry.
     */
    private function resolveRange(Request $request): int
    {
        $validated = $request->validate([
            'range' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        return (int) ($validated['range'] ?? 30);
    }

    private function registrationLabels(int $range): array
    {
        return collect(range($range - 1, 0))
            ->map(fn ($daysAgo) => now()->subDays($daysAgo)->format('M d'))
            ->toArray();
    }

    private function registrationCounts(int $range): array
    {
        $counts = User::where('created_at', '>=', now()->subDays($range - 1)->startOfDay())
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range($range - 1, 0))
            ->map(function ($daysAgo) use ($counts) {
                $date = now()->subDays($daysAgo)->format('Y-m-d');

                return $counts[$date] ?? 0;
            })
            ->toArray();
    }

    private function getDashboardData(int $range): array
    {
        $cacheKey = "admin.dashboard.range.{$range}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($range) {
            $totalUsers = User::count();

            $activeUsers = User::where('last_active_at', '>=', now()->subDays(7))->count();

            $newRegistrations30d = User::where('created_at', '>=', now()->subDays(30))->count();

            $usersByCity = City::whereHas('studentData')
                ->withCount('studentData')
                ->get();

            $usersBySchool = StudentData::selectRaw('school_id, count(*) as total')
                ->groupBy('school_id')
                ->get();

            $topForums = Forum::withCount('threads')
                ->orderByDesc('threads_count')
                ->limit(5)
                ->get();

            $registrationLabels = $this->registrationLabels($range);
            $registrationCounts = $this->registrationCounts($range);

            return compact(
                'totalUsers',
                'activeUsers',
                'newRegistrations30d',
                'usersBySchool',
                'usersByCity',
                'topForums',
                'registrationCounts',
                'registrationLabels'
            );
        });
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('export dashboard');

        $range = $this->resolveRange($request);

        $pdf = Pdf::loadView('admin.dashboard.export', $this->getDashboardData($range));

        return $pdf->download('dashboard-report-'.now()->format('Y-m-d').'.pdf');
    }
}
