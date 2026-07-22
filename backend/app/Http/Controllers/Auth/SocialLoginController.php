<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    // 1. Redirect to Google/Facebook
    public function redirect($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    // 2. Handle callback from Google/Facebook
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            $email = $socialUser->getEmail() ?: sprintf('%s-%s@social.local', $provider, $socialUser->getId());

            $user = User::firstOrNew(['email' => $email]);
            $user->fill([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
            $user->save();

            // Log the user into the session so Sanctum's SPA (cookie) auth takes
            // over. No token is exposed to the frontend; the browser holds an
            // httpOnly session cookie that JavaScript cannot read.
            Auth::login($user, remember: true);
            request()->session()->regenerate();

            $onboardingStatus = $user->onboarding_completed_at ? 'complete' : 'required';

            $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'));

            return redirect()->to("{$frontendUrl}/auth/callback?onboarding={$onboardingStatus}");

        } catch (\Exception $e) {
            Log::error('Social login failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);

            $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'));

            return redirect()->to("{$frontendUrl}/login?error=auth_failed");
        }
    }
}
