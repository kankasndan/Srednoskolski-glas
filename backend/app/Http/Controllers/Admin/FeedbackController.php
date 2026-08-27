<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Notifications\NewFeedbackNotification;
use App\Support\LikeEscape;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view feedback');

        $filters = $this->filters($request);
        $items = $this->filteredQuery($filters)
            ->with('user')
            ->orderByRaw('reviewed_at is null desc')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Feedback::query()->count(),
            'new' => Feedback::unreviewedCount(),
            'this_week' => Feedback::query()->where('created_at', '>=', now()->startOfWeek())->count(),
            'average' => round((float) (Feedback::query()->avg('rating') ?? 0), 1),
            'guests' => Feedback::query()->whereNull('user_id')->count(),
            'members' => Feedback::query()->whereNotNull('user_id')->count(),
            'low' => Feedback::query()->where('rating', '<=', 2)->whereNull('reviewed_at')->count(),
        ];

        $counts = Feedback::query()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $distribution = [];
        foreach (range(5, 1) as $star) {
            $distribution[$star] = (int) ($counts[$star] ?? $counts[(string) $star] ?? 0);
        }

        return view('admin.feedback.index', [
            'items' => $items,
            'stats' => $stats,
            'distribution' => $distribution,
            'filters' => $filters,
            'isFiltered' => $this->isFiltered($filters),
            'queryBase' => $this->queryBase($filters),
        ]);
    }

    public function show(Request $request, Feedback $feedback)
    {
        $this->authorize('view feedback');

        $feedback->load([
            'user.studentData.school',
            'reviewer',
        ]);

        $backQuery = $this->queryBase($this->filters($request));

        return view('admin.feedback.show', compact('feedback', 'backQuery'));
    }

    public function review(Request $request, Feedback $feedback)
    {
        $this->authorize('review feedback');

        if ($feedback->isReviewed()) {
            return redirect()
                ->route('feedback.index', $this->queryBase($this->filters($request)))
                ->withErrors(['status' => 'Мислењето е веќе прегледано.']);
        }

        $feedback->update([
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        NewFeedbackNotification::markTargetRead($feedback);

        return redirect()
            ->route('feedback.index', $this->queryBase($this->filters($request)))
            ->with('success', 'Мислењето е означено како прегледано.');
    }

    public function unreview(Request $request, Feedback $feedback)
    {
        $this->authorize('review feedback');

        $feedback->update([
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        return redirect()
            ->route('feedback.show', array_merge(['feedback' => $feedback], $this->queryBase($this->filters($request))))
            ->with('success', 'Мислењето е вратено во новите.');
    }

    public function note(Request $request, Feedback $feedback)
    {
        $this->authorize('review feedback');

        $validated = $request->validate([
            'staff_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $note = isset($validated['staff_note']) ? trim((string) $validated['staff_note']) : '';

        $feedback->update([
            'staff_note' => $note === '' ? null : $note,
        ]);

        return redirect()
            ->route('feedback.show', array_merge(['feedback' => $feedback], $this->queryBase($this->filters($request))))
            ->with('success', 'Белешката е зачувана.');
    }

    public function destroy(Request $request, Feedback $feedback)
    {
        $this->authorize('delete feedback');

        NewFeedbackNotification::markTargetRead($feedback);
        $feedback->delete();

        return redirect()
            ->route('feedback.index', $this->queryBase($this->filters($request)))
            ->with('success', 'Мислењето е избришано.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('view feedback');

        $filters = $this->filters($request);
        $filename = 'feedback-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['id', 'rating', 'message', 'username', 'created_at', 'reviewed_at', 'staff_note']);

            $this->filteredQuery($filters)
                ->with('user')
                ->orderBy('id')
                ->chunk(200, function ($rows) use ($handle): void {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->id,
                            $row->rating,
                            $row->message,
                            $row->user?->username ?? 'Гостин',
                            $row->created_at?->toDateTimeString(),
                            $row->reviewed_at?->toDateTimeString(),
                            $row->staff_note,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{q: string, rating: int|null, status: string, range: string}
     */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'status' => ['nullable', 'in:all,new,reviewed'],
            'range' => ['nullable', 'in:all,7,30,week'],
        ]);

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'rating' => isset($validated['rating']) ? (int) $validated['rating'] : null,
            'status' => $validated['status'] ?? 'new',
            'range' => $validated['range'] ?? 'all',
        ];
    }

    /**
     * @param  array{q: string, rating: int|null, status: string, range: string}  $filters
     */
    private function filteredQuery(array $filters): Builder
    {
        return Feedback::query()
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $like = LikeEscape::contains($filters['q']);
                $query->where(function ($inner) use ($like) {
                    $inner->where('message', 'like', $like)
                        ->orWhereHas('user', fn ($users) => $users->where('username', 'like', $like));
                });
            })
            ->when($filters['rating'] !== null, fn ($query) => $query->where('rating', $filters['rating']))
            ->when($filters['status'] === 'new', fn ($query) => $query->whereNull('reviewed_at'))
            ->when($filters['status'] === 'reviewed', fn ($query) => $query->whereNotNull('reviewed_at'))
            ->when($filters['range'] === '7', fn ($query) => $query->where('created_at', '>=', now()->subDays(7)))
            ->when($filters['range'] === '30', fn ($query) => $query->where('created_at', '>=', now()->subDays(30)))
            ->when($filters['range'] === 'week', fn ($query) => $query->where('created_at', '>=', now()->startOfWeek()));
    }

    /**
     * @param  array{q: string, rating: int|null, status: string, range: string}  $filters
     * @return array<string, string|int>
     */
    private function queryBase(array $filters): array
    {
        return array_filter([
            'q' => $filters['q'] !== '' ? $filters['q'] : null,
            'rating' => $filters['rating'],
            'status' => $filters['status'] !== 'new' ? $filters['status'] : null,
            'range' => $filters['range'] !== 'all' ? $filters['range'] : null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param  array{q: string, rating: int|null, status: string, range: string}  $filters
     */
    private function isFiltered(array $filters): bool
    {
        return $filters['q'] !== ''
            || $filters['rating'] !== null
            || $filters['status'] !== 'new'
            || $filters['range'] !== 'all';
    }
}
