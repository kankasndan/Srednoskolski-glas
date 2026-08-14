<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\User;
use App\Support\StaffRoleHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('view roles page');

        $users = User::orderBy('role')->with('forum')->get();

        $roles = User::distinct()->get('role');

        $forums = Forum::get();

        $assignableRoles = StaffRoleHierarchy::assignableRoles(Auth::user());

        return view('admin.roles.index', compact('users', 'forums', 'roles', 'assignableRoles'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update user role');

        $actor = Auth::user();
        abort_unless($actor !== null && StaffRoleHierarchy::canManage($actor, $user), 403);

        $validated = $request->validate([
            'role' => ['required', Rule::in(StaffRoleHierarchy::assignableRoles($actor))],
        ]);

        if ($user->role === 'moderator' && in_array($validated['role'], ['admin', 'super_admin'], true)) {
            Forum::where('user_id', $user->id)->update([
                'user_id' => null,
            ]);
        }

        $user->forceFill(['role' => $validated['role']])->save();
        $user->syncRoles([$validated['role']]);

        return back()->with(['success' => 'Корисникот е успешно ажуриран.']);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete user role');

        $actor = Auth::user();
        abort_unless($actor !== null && StaffRoleHierarchy::canManage($actor, $user), 403);

        Forum::where('user_id', $user->id)->update([
            'user_id' => null,
        ]);

        $user->forceFill(['role' => 'user'])->save();
        $user->syncRoles(['user']);

        return back()->with(['success' => 'Улогата е одземена. Корисникот е вратен на обичен корисник.']);
    }

    public function liveSearch(Request $request)
    {
        $this->authorize('view roles page');

        $query = $request->get('q', '');

        $users = User::where('username', 'like', "%{$query}%")
            ->whereIn('role', ['moderator', 'admin', 'super_admin'])
            ->limit(10)
            ->get(['id', 'username', 'email', 'role']);

        return response()->json($users);
    }

    public function show(User $user)
    {
        $this->authorize('view roles page');

        $forums = Forum::get();
        $assignableRoles = StaffRoleHierarchy::assignableRoles(Auth::user());
        $canManage = Auth::user() !== null && StaffRoleHierarchy::canManage(Auth::user(), $user);

        return view('admin.roles.show', compact('user', 'forums', 'assignableRoles', 'canManage'));
    }

    public function grantSearch(Request $request)
    {
        $this->authorize('grant roles');

        $query = $request->get('q', '');

        $users = User::where('username', 'like', "%{$query}%")
            ->where('role', 'user')
            ->limit(10)
            ->get(['id', 'username', 'email', 'role']);

        return response()->json($users);
    }

    public function grant(Request $request)
    {
        $this->authorize('grant roles');

        $actor = Auth::user();
        abort_unless($actor !== null, 403);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => ['required', Rule::in(StaffRoleHierarchy::assignableRoles($actor))],
        ]);

        $user = User::findOrFail($validated['user_id']);

        abort_unless(StaffRoleHierarchy::canManage($actor, $user), 403);

        $user->forceFill(['role' => $validated['role']])->save();
        $user->syncRoles([$validated['role']]);

        return back()->with(['success' => "Улогата е успешно доделена на {$user->username}."]);
    }

    public function updateForum(Request $request)
    {
        $this->authorize('update forum role settings');

        $actor = Auth::user();
        abort_unless($actor !== null, 403);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'forum' => 'required|exists:forums,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        abort_unless(StaffRoleHierarchy::canManage($actor, $user), 403);
        abort_unless($user->role === 'moderator', 403);

        Forum::where('user_id', $validated['user_id'])->update([
            'user_id' => null,
        ]);

        $forum = Forum::findOrFail($validated['forum']);

        $forum->update([
            'user_id' => $validated['user_id'],
        ]);

        if (str_contains(url()->previous(), 'show')) {
            return redirect()->route('role.show', ['user' => $validated['user_id']])->with('success', 'Модераторот е успешно доделен.');
        }

        return redirect()->route('role.index')->with('success', 'Модераторот е успешно доделен.');
    }
}
