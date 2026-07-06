<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Simtabi\Laranail\Auth\Enums\SocialProviderEnum;

class SocialLoginController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        $socialProvider = SocialProviderEnum::tryFrom($provider);

        if (! $socialProvider || ! $this->isProviderEnabled($provider)) {
            return redirect()->route('login')->withErrors([
                'provider' => 'The given provider is not supported.',
            ]);
        }

        session(['auth-kit.provider' => $provider]);

        return Socialite::driver($provider)
            ->scopes($socialProvider->getScopes())
            ->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $socialProvider = SocialProviderEnum::tryFrom($provider);

        if (! $socialProvider || ! $this->isProviderEnabled($provider)) {
            return redirect()->route('login')->withErrors([
                'provider' => 'The given provider is not supported.',
            ]);
        }

        session()->forget('auth-kit.provider');

        $request = new Request(['provider' => $provider, 'code' => true]);

        $method = app(\Simtabi\Laranail\Auth\Methods\SocialLoginMethod::class);

        $user = $method->authenticate($request);

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to authenticate with '.$provider.'.',
            ]);
        }

        return redirect()->intended(config('auth-kit.redirect_after_social_login', '/'));
    }

    private function isProviderEnabled(string $provider): bool
    {
        $enabledProviders = config('auth-kit.social.providers', []);

        return isset($enabledProviders[$provider]);
    }
}
