<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Methods;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Simtabi\Laranail\Auth\Models\Social;
use Simtabi\Laranail\Auth\Support\AuthConfig;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Enums\SocialProviderEnum;
use Simtabi\Laranail\Auth\Events\SocialLoginAttempt;
use Simtabi\Laranail\Auth\Events\SocialLoginSuccess;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SocialLoginMethod extends BaseAuthMethod
{
    public function getName(): string
    {
        return 'social';
    }

    public function canHandle(Request $request): bool
    {
        return $request->has('provider') && $request->has('code');
    }

    public function validate(Request $request): bool
    {
        $request->validate([
            'provider' => ['required', 'string', 'in:'.implode(',', array_column(SocialProviderEnum::cases(), 'value'))],
        ]);

        return true;
    }

    public function authenticate(Request $request): ?Authenticatable
    {
        $provider = SocialProviderEnum::tryFrom($request->input('provider'));

        if (! $provider) {
            return null;
        }

        try {
            $socialUser = Socialite::driver($provider->value)->user();
            $guard = $this->resolveGuard();
            $model = $this->resolveModel();

            SocialLoginAttempt::dispatch(
                provider: $provider->value,
                providerId: $socialUser->getId(),
                guard: $guard,
            );

            $socialAccount = Social::where('provider', $provider->value)
                ->where('provider_id', $socialUser->getId())
                ->first();

            $user = null;
            $isNewUser = false;

            if ($socialAccount) {
                $socialAccount->update([
                    'name'          => $socialUser->getName(),
                    'nickname'      => $socialUser->getNickname(),
                    'email'         => $socialUser->getEmail(),
                    'avatar_path'   => $socialUser->getAvatar(),
                    'token'         => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'expires_at'    => $socialUser->expiresIn
                        ? now()->addSeconds($socialUser->expiresIn)
                        : null,
                ]);

                $user = $socialAccount->user;
            }

            if (! $user) {
                $user = $model::where('email', $socialUser->getEmail())->first();

                if (! $user) {
                    $user = $model::create([
                        'first_name'        => $this->extractFirstName($socialUser->getName() ?? $socialUser->getNickname() ?? 'User'),
                        'last_name'         => $this->extractLastName($socialUser->getName() ?? $socialUser->getNickname() ?? 'User'),
                        'email'             => $socialUser->getEmail(),
                        'email_verified_at' => now(),
                    ]);
                    $isNewUser = true;
                }

                Social::create([
                    'user_id'       => $user->id,
                    'provider'      => $provider->value,
                    'provider_id'   => $socialUser->getId(),
                    'name'          => $socialUser->getName(),
                    'nickname'      => $socialUser->getNickname(),
                    'email'         => $socialUser->getEmail(),
                    'avatar_path'   => $socialUser->getAvatar(),
                    'token'         => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'expires_at'    => $socialUser->expiresIn
                        ? now()->addSeconds($socialUser->expiresIn)
                        : null,
                ]);
            }

            Auth::guard($guard)->login($user, true);

            SocialLoginSuccess::dispatch(
                user: $user,
                provider: $provider->value,
                providerId: $socialUser->getId(),
                isNewUser: $isNewUser,
                guard: $guard,
            );

            return $user;

        } catch (Exception) {
            return null;
        }
    }

    public function redirect(): ?RedirectResponse
    {
        $provider = request()->route('provider');

        if (! $provider || ! $this->isProviderEnabled($provider)) {
            return null;
        }

        return Socialite::driver($provider)->redirect();
    }

    public function getConfig(): array
    {
        return [
            'guard' => $this->guard,
        ];
    }

    protected function isProviderEnabled(string $provider): bool
    {
        $enabledProviders = $this->config('providers', []);

        return in_array($provider, $enabledProviders);
    }

    private function resolveGuard(): string
    {
        return AuthConfig::fromGuard($this->guard)->guard;
    }

    private function resolveModel(): string
    {
        return AuthConfig::fromGuard($this->guard)->model;
    }

    private function extractFirstName(string $fullName): string
    {
        $names = explode(' ', $fullName, 2);

        return $names[0] ?? 'Social';
    }

    private function extractLastName(string $fullName): string
    {
        $names = explode(' ', $fullName, 2);

        return $names[1] ?? 'User';
    }
}
