<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            // Retrieve user data from the provider
            $socialUser = Socialite::driver($provider)->user();
            
            // Register or log in the user
            $user = User::updateOrCreate(
                [
                    'email' => $socialUser->getEmail(),
                ],
                [
                    'name' => $socialUser->getName(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]
            );

            // Authenticate the user into your application
            Auth::login($user);

            // Redirect to your intended dashboard route
            return redirect('/dashboard'); 
            
        } catch (\Exception $e) {
            dd('failed');
            return redirect('/login')->withErrors(['error' => 'Authentication failed. Please try again.']);
        }
    }
}
