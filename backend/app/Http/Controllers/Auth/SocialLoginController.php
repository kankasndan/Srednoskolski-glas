<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SocialLoginController extends Controller
{
    /** @var list<string> */
    private const ALLOWED_PROVIDERS = ['google', 'facebook'];

    public function redirect(string $provider): RedirectResponse
    {
        $this->assertAllowedProvider($provider);

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->assertAllowedProvider($provider);

        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $providerId = (string) $socialUser->getId();
            $email = $socialUser->getEmail() ?: sprintf('%s-%s@social.local', $provider, $providerId);

            // Prefer stable provider identity over email alone (avoids takeover via email clash).
            $user = User::query()
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();

            if ($user === null) {
                $user = User::query()->firstOrNew(['email' => $email]);
            }

            $user->fill([
                'email' => $user->email ?: $email,
                'provider' => $provider,
                'provider_id' => $providerId,
            ]);
            $user->save();

            // Log the user into the session so Sanctum's SPA (cookie) auth takes
            // over. No token is exposed to the frontend; the browser holds an
            // httpOnly session cookie that JavaScript cannot read.
            Auth::login($user, remember: true);
            request()->session()->regenerate();

            $onboardingStatus = $user->onboarding_completed_at ? 'complete' : 'required';

            return redirect()->to("{$frontendUrl}/auth/callback?onboarding={$onboardingStatus}");
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Social login failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);

            return redirect()->to("{$frontendUrl}/login?error=auth_failed");
        }
    }

    private function assertAllowedProvider(string $provider): void
    {
        if (! in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            throw new NotFoundHttpException('Unsupported auth provider.');
        }
    }
}
