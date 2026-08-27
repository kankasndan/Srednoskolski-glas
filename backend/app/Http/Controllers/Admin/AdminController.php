<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Media;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Username;
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
        $this->authorize('view own profile');
        abort_unless($request->user()?->is($user), 403);

        return view('admin.myprofile.index', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update own profile');
        // Self-service only — never allow editing another user's profile.
        abort_unless($request->user()?->is($user), 403);

        $validated = $request->validate([
            'username' => Username::rules($user->id),
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ]);

        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        return redirect()
            ->route('admin.profile', $user)
            ->with('success', 'Профилот е успешно ажуриран.');
    }

    public function updatePassword(User $user, Request $request)
    {
        $this->authorize('update own password');
        // Self-service only — changing another user's password is not allowed here.
        abort_unless($request->user()?->is($user), 403);
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->uncompromised()->mixedCase()->numbers()->symbols()],
        ]);

        $user->update([
            'password' => $request['password'],
        ]);

        return back()->with(['success' => 'Лозинката е успешно ажурирана.']);
    }

    public function updateImages(User $user, Request $request)
    {
        $this->authorize('update own profile images');
        abort_unless($request->user()?->is($user), 403);

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $uploaded = $validated['image'];
        if (! $uploaded instanceof UploadedFile) {
            return back()->withErrors(['image' => 'Прикачи валидна слика.']);
        }

        $imageUrl = Media::upload($uploaded, 'users/images')->url;

        $user->update([
            'imageUrl' => $imageUrl,
        ]);

        return back()->with(['success' => 'Профилната слика е успешно ажурирана.']);
    }

    public function readAllNotifications()
    {
        Auth::user()?->unreadNotifications->markAsRead();

        return back();
    }

    public function notifications()
    {
        $notifications = Auth::user()->notifications()->latest('updated_at')->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function readNotification(string $id)
    {
        $notification = Auth::user()?->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return redirect($this->safeNotificationUrl($notification->data['url'] ?? null));
    }

    /**
     * Notification payloads are app-generated today, but a stored redirect target
     * is still data: only ever follow a same-origin URL.
     */
    private function safeNotificationUrl(mixed $url): string
    {
        $fallback = route('report.index');

        if (! is_string($url) || $url === '') {
            return $fallback;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return url($url);
        }

        $host = parse_url($url, PHP_URL_HOST);

        if ($host !== null && $host === parse_url((string) config('app.url'), PHP_URL_HOST)) {
            return $url;
        }

        return $fallback;
    }
}
