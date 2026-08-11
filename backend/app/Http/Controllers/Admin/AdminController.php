<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Media;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function profile(User $user, Request $request)
    {
        abort_unless($request->user()?->is($user), 403);

        return view('admin.myprofile.index', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        abort_unless($request->user()?->is($user), 403);

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
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->uncompromised()->mixedCase()->numbers()->symbols()],
        ]);

        $user->update([
            'password' => $request['password'],
        ]);

        return back()->with(['success' => "Successfully updated password"]);
    }

    public function updateImages(User $user, Request $request)
    {
        $request->validate([
            'imageUrl' => ['nullable', 'image', 'max:5120'],
        ]);

        $imageUrl = $user->imageUrl;

        if ($request->file('image') instanceof UploadedFile) {
            $imageUrl = Media::upload($request->file('image'), 'users/images')->url;
            dd($imageUrl);
        }
            

        // $user->update([
        //     'imageUrl' => $imageUrl
        // ]);

        // return back()->with(['success' => "Successfully updated image"]);
    }

    public function readAllNotifications()
    {
        Auth::user()?->unreadNotifications->markAsRead();

        return back();
    }
}
