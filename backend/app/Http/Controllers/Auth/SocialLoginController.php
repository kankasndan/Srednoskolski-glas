<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
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

        // Stateful on purpose: Socialite stores a `state` value in the session and
        // verifies it on callback, which is what stops an attacker from replaying
        // their own authorization code into a victim's browser (login CSRF).
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->assertAllowedProvider($provider);

        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

        try {
            $socialUser = Socialite::driver($provider)->user();
            $providerId = (string) $socialUser->getId();
            $email = $socialUser->getEmail();

            [$user, $error] = $this->resolveSocialUser($provider, $providerId, $email);

            if ($error !== null || $user === null) {
                return redirect()->to("{$frontendUrl}/login?error=".($error ?? 'auth_failed'));
            }

            // Log the user into the session so Sanctum's SPA (cookie) auth takes
            // over. No token is exposed to the frontend; the browser holds an
            // httpOnly session cookie that JavaScript cannot read.
            Auth::login($user, remember: false);
            request()->session()->regenerate();

            $onboardingStatus = $user->onboarding_completed_at ? 'complete' : 'required';

            return redirect()->to("{$frontendUrl}/auth/callback?onboarding={$onboardingStatus}");
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Social login failed', [
                'provider' => $provider,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return redirect()->to("{$frontendUrl}/login?error=auth_failed");
        }
    }

    /**
     * Resolve the local user for this social identity.
     *
     * Lookup is only by provider + provider_id. A colliding email is refused
     * rather than attached to (or overwriting) an existing account.
     *
     * @return array{0: User|null, 1: string|null}
     */
    private function resolveSocialUser(string $provider, string $providerId, ?string $email): array
    {
        $user = User::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($user !== null) {
            return [$user, null];
        }

        $email = $this->normalizedSocialEmail($provider, $providerId, $email);

        if (User::query()->where('email', $email)->exists()) {
            return [null, 'email_in_use'];
        }

        try {
            $user = (new User)->forceFill([
                'email' => $email,
                'provider' => $provider,
                'provider_id' => $providerId,
            ]);
            $user->save();
        } catch (UniqueConstraintViolationException) {
            $user = User::query()
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();

            if ($user !== null) {
                return [$user, null];
            }

            return [null, 'email_in_use'];
        }

        return [$user, null];
    }

    private function normalizedSocialEmail(string $provider, string $providerId, ?string $email): string
    {
        $email = strtolower(trim((string) $email));

        if ($email !== '') {
            return $email;
        }

        return sprintf('%s-%s@social.local', $provider, $providerId);
    }

    private function assertAllowedProvider(string $provider): void
    {
        if (! in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            throw new NotFoundHttpException('Unsupported auth provider.');
        }
    }
}
