<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function index()
    {
        return view("admin.dashboard");
    }

    public function profile(User $user)
    {
        return view("admin.myprofile.index", compact("user"));
    }

    public function update(User $user, Request $request)
    {
        $user->update([
            "username" => $request->username,
            "email" => $request->email,
        ]);

        return view("admin.myprofile.index", compact("user"));
    }

    public function updatePassword(User $user, Request $request)
    {
        $request->validate([
            "password" => ["required", "confirmed", Password::min(8)->uncompromised()->mixedCase()->numbers()->symbols()],
        ]);

        if(Hash::check($request->current_password, $user->password)){
            $user->update([
                "password" => $request->current_password
            ]);
        } else{
            return back()->withErrors([
                "invalid_password" => "Invalid current password!"
            ]);
        }

        return back();
    }
}
