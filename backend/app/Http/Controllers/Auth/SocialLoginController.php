<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Log;

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

            $user = User::updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ],
                [
                    'username' => $socialUser->getName(),
                    'email' => $email,
                    'provider' => $provider,
                ]
            );

            $token = $user->createToken('auth_token')->plainTextToken;

            $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'));

            return redirect()->to("{$frontendUrl}/auth/callback?token={$token}");

        } catch (\Exception $e) {
            $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'));

            return redirect()->to("{$frontendUrl}/login?error=auth_failed");
        }
    }
}
