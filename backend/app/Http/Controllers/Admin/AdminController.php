<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function profile(User $user)
    {
        return view('admin.myprofile.index', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $user->update([
            'username' => $request->username,
            'email' => $request->email,
        ]);

        return view('admin.myprofile.index', compact('user'));
    }

    public function updatePassword(User $user, Request $request)
    {
        // Self-service only — changing another user's password is not allowed here.
        abort_unless($request->user()?->is($user), 403);

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->uncompromised()->mixedCase()->numbers()->symbols()],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        return back();
    }

    public function readAllNotifications()
    {
        Auth::user()?->unreadNotifications->markAsRead();

        return back();
    }
}
