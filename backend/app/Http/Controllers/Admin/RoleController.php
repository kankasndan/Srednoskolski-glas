<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $users = User::orderBy("role")->get();

        return view("admin.roles.index", compact("users"));
    }

    public function update(User $user, Request $request)
    {
        $user->update(["role" => $request->role]);

        return back()->with(["success" => "User successfully updated."]);
    }

    public function destroy(User $user)
    {
        $user->update(["role" => "user"]);

        return back()->with(["success" => "User successfully granted role 'user'."]);
    }

    public function liveSearch(Request $request)
    {
        $query = $request->get('q', '');

        $users = User::where('email', 'like', "%{$query}%")
            ->whereIn('role', ['moderator', 'admin', 'super_admin'])
            ->limit(10)
            ->get(['id', 'username', 'email', 'role']);

        return response()->json($users);
    }

    public function show(User $user)
    {
        return view("admin.roles.show", compact("user"));
    }

    public function grantSearch(Request $request)
    {
        $query = $request->get('q', '');

        $users = User::where('email', 'like', "%{$query}%")
            ->where('role', 'user')
            ->limit(10)
            ->get(['id', 'username', 'email', 'role']);

        return response()->json($users);
    }

    public function grant(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:super_admin,admin,moderator',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update(['role' => $request->role]);

        return back()->with(['success' => "Role granted successfully to {$user->username}."]);
    }
}
