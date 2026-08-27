<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\User;
use App\Support\LikeEscape;
use App\Support\StaffRoleHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('view roles page');

        $staffByRole = User::query()
            ->whereIn('role', ['super_admin', 'admin', 'moderator'])
            ->orderBy('username')
            ->with('forum')
            ->get()
            ->groupBy('role');

        $roleOrder = ['super_admin', 'admin', 'moderator'];

        $forums = Forum::get();

        $assignableRoles = StaffRoleHierarchy::assignableRoles(Auth::user());

        return view('admin.roles.index', compact('staffByRole', 'roleOrder', 'forums', 'assignableRoles'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update user role');

        $actor = Auth::user();
        abort_unless($actor !== null && StaffRoleHierarchy::canManage($actor, $user), 403);

        $validated = $request->validate([
            'role' => ['required', Rule::in(StaffRoleHierarchy::assignableRoles($actor))],
        ]);

        $this->assignRole($user, $validated['role']);

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

        return redirect()->route('role.index')->with(['success' => 'Улогата е одземена. Корисникот е вратен на обичен корисник.']);
    }

    public function liveSearch(Request $request)
    {
        $this->authorize('view roles page');

        $query = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $users = User::where('username', 'like', LikeEscape::contains($query))
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

        $query = mb_substr(trim((string) $request->get('q', '')), 0, 100);

        $users = User::where('username', 'like', LikeEscape::contains($query))
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

        if ($user->role === $validated['role']) {
            return back()->withErrors(['role' => "{$user->username} веќе ја има оваа улога."]);
        }

        $this->assignRole($user, $validated['role']);

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

        $forum = Forum::findOrFail($validated['forum']);

        // Never silently take a forum away from another moderator: unassign them
        // explicitly first, so the change is visible in the roles screen.
        if ($forum->user_id !== null && (int) $forum->user_id !== (int) $validated['user_id']) {
            return back()->withErrors([
                'forum' => 'Форумот веќе има модератор. Прво одземи му го форумот.',
            ]);
        }

        Forum::where('user_id', $validated['user_id'])->update([
            'user_id' => null,
        ]);

        $forum->update([
            'user_id' => $validated['user_id'],
        ]);

        if (str_contains(url()->previous(), 'show')) {
            return redirect()->route('role.show', ['user' => $validated['user_id']])->with('success', 'Модераторот е успешно доделен.');
        }

        return redirect()->route('role.index')->with('success', 'Модераторот е успешно доделен.');
    }

    /**
     * Single place where a staff role is written, so forum ownership can never
     * outlive the moderator role it belongs to.
     */
    private function assignRole(User $user, string $role): void
    {
        if ($user->role === 'moderator' && $role !== 'moderator') {
            Forum::where('user_id', $user->id)->update([
                'user_id' => null,
            ]);
        }

        $user->forceFill(['role' => $role])->save();
        $user->syncRoles([$role]);
    }
}
