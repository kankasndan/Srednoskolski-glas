<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Email;

class AuthContoller extends Controller
{
    public function index()
    {
        return view("admin.auth.index");
    }

    public function login(Request $request)
    {

        $credentials = $request->validate([
            "email" => ['required', "email"],
            "password" => ['required']
        ]);

        $remember = $request->boolean("remember");

        if(Auth::attempt($credentials, $remember)){
            $request->session()->regenerate();
            return redirect()->route("admin.dashboard");
        }

        return back()->withErrors(["credentials" => "Invalid credentials!"]);
    }
}
