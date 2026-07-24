<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $users = User::orderBy("role")->with("forum")->get();

        $roles = User::distinct()->get("role");

        $forums = Forum::get();

        return view("admin.roles.index", compact("users", "forums", "roles"));
    }

    public function update(User $user, Request $request)
    {
        if($user->role == "moderator"){
            if($request->role == "admin" || $request->role == "super-admin"){
                $forum = Forum::where("user_id", $user->id)->update([
                    "user_id" => null
                ]);;
            }
        }

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

        $users = User::where('username', 'like', "%{$query}%")
            ->whereIn('role', ['moderator', 'admin', 'super_admin'])
            ->limit(10)
            ->get(['id', 'username', 'email', 'role']);

        return response()->json($users);
    }

    public function show(User $user)
    {
        $forums = Forum::get();

        return view("admin.roles.show", compact("user", "forums"));
    }

    public function grantSearch(Request $request)
    {
        $query = $request->get('q', '');

        $users = User::where('username', 'like', "%{$query}%")
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

    public function updateForum(Request $request)
    {
        $forum = Forum::where("user_id", $request->user_id);

        if($forum){
            $forum->update([
                'user_id' => null,
            ]);
        }

        $forum = Forum::findOrFail($request->forum);

        $forum->update([
            'user_id' => $request->user_id,
        ]);

        if(str_contains(url()->previous(), "show")){
            return redirect()->route('role.show', ["user" => $request->user_id])->with('success', 'Moderator assigned successfully.');
        }

        return redirect()->route('role.index')->with('success', 'Moderator assigned successfully.');
    }

    
}
