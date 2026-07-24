<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with([
                'studentData.school.city',
                'sanctions',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('school'), function ($query) use ($request) {
                $query->whereHas('studentData', function ($q) use ($request) {
                    $q->where('school_id', $request->get('school'));
                });
            })
            ->when($request->filled('sign_in_method'), function ($query) use ($request) {
                $query->where('provider', $request->get('sign_in_method'));
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
            ->where("role", "user")
            ->paginate(20)
            ->withQueryString();

        $schools = School::orderBy("name")->get();

        return view("admin.users.index", compact("users", "schools"));
    }

    public function liveSearch(Request $request)
    {
        $query = $request->get('q', '');

        $users = User::where('username', 'like', "%{$query}%")
            ->where('role', "user")
            ->limit(10)
            ->get(['id', 'username', 'email', 'role']);

        return response()->json($users);
    }

    public function show(User $user)
    {
        $user->whereHas("studentData")->with(["studentData.school.city", "sanctions", "forums", "threads, topics"]);

        return view("admin.users.show", compact("user"));
    }



    public function export(User $user)
    {
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
}
