<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function redirecting()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('login');
    }

    public function index()
    {
        return view('admin.auth.index');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['credentials' => 'Invalid credentials!']);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user === null || ! $user->can('access admin panel')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'credentials' => 'You do not have access to the admin panel.',
            ]);
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
