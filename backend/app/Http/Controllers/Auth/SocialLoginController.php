<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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
            
            // Find existing user or create a new one
            $user = User::updateOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName(),
                    'provider_id' => $socialUser->getId(),
                    'provider_name' => $provider,
                ]
            );

            // Create Sanctum API Token for React
            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect browser back to React frontend callback page with the token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->to("{$frontendUrl}/auth/callback?token={$token}");

        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->to("{$frontendUrl}/login?error=auth_failed");
        }
    }
}