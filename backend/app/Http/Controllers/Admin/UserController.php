<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\LikeEscape;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view users');

        $users = User::query()
            ->with([
                'studentData.school.city',
                'sanctions',
                'threads.forum',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = LikeEscape::contains((string) $request->get('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($request->filled('school'), function ($query) use ($request) {
                $query->whereHas('studentData', function ($q) use ($request) {
                    $q->where('school_id', $request->get('school'));
                });
            })
            ->when($request->filled('provider'), function ($query) use ($request) {
                $query->where('provider', $request->get('provider'));
            })
            ->when($request->get('status') === 'banned', function ($query) {
                $query->whereHas('sanctions', function ($q) {
                    $q->where('type', '!=', 'warning')
                        ->where(function ($sq) {
                            $sq->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                });
            })
            ->when($request->get('status') === 'active', function ($query) {
                $query->whereDoesntHave('sanctions', function ($q) {
                    $q->where('type', '!=', 'warning')
                        ->where(function ($sq) {
                            $sq->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                });
            })
            ->where('role', 'user')
            ->paginate(10)
            ->withQueryString();

        $schools = School::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'schools'));
    }

    public function liveSearch(Request $request)
    {
        $this->authorize('search users');

        $query = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $users = User::where('username', 'like', LikeEscape::contains($query))
            ->where('role', 'user')
            ->limit(10)
            ->get(['id', 'username', 'email', 'role']);

        return response()->json($users);
    }

    public function show(User $user)
    {
        $this->authorize('view user details');
        abort_unless(in_array($user->role, ['user', 'moderator', 'admin', 'super_admin'], true), 404);

        if ($user->role !== 'user') {
            return redirect()->route('role.show', $user);
        }

        $user->load(['studentData.school.city', 'sanctions', 'forums', 'threads', 'topics']);

        return view('admin.users.show', compact('user'));
    }

    public function export(User $user)
    {
        $this->authorize('export user as pdf');
        abort_unless($user->role === 'user', 404);

        $user->load([
            'studentData.school.city',
            'studentData.vocation',
            'sanctions',
            'topics',
            'roles',
        ]);

        $pdf = Pdf::loadView('admin.users.export', ['user' => $user]);

        return $pdf->download("user-{$user->id}-report.pdf");
    }

    public function getSanctionStatusAttribute(): string
    {
        $activeBan = $this->sanctions
            ->where('type', '!=', 'warning')
            ->first(function ($sanction) {
                return $sanction->expires_at === null
                    || $sanction->expires_at->isFuture();
            });

        if ($activeBan) {
            return 'banned';
        }

        if ($this->sanctions->where('type', 'warning')->isNotEmpty()) {
            return 'warning';
        }

        return 'active';
    }
}
