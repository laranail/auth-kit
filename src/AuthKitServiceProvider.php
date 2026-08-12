<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AuthKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(path: __DIR__ . '/../config/auth-kit.php', key: 'auth-kit');

        // Auth actions
        $this->app->bind(abstract: Contracts\AttemptEmailPasswordLoginInterface::class, concrete: Actions\AttemptEmailPasswordLogin::class);
        $this->app->bind(abstract: Contracts\CheckEmailExistsInterface::class, concrete: Actions\CheckEmailExists::class);
        $this->app->bind(abstract: Contracts\FindUserByEmailInterface::class, concrete: Actions\FindUserByEmail::class);
        $this->app->bind(abstract: Contracts\LoginUserInterface::class, concrete: Actions\LoginUser::class);
        $this->app->bind(abstract: Contracts\CreateNewUserInterface::class, concrete: Actions\CreateNewUser::class);

        $this->app->bind(abstract: Contracts\LogoutUserInterface::class, concrete: Actions\LogoutUser::class);

        // Social actions
        $this->app->bind(abstract: Contracts\SocialRedirectActionInterface::class, concrete: Actions\SocialRedirectAction::class);
        $this->app->bind(abstract: Contracts\SocialCallbackActionInterface::class, concrete: Actions\SocialCallbackAction::class);
        $this->app->bind(abstract: Contracts\CreateSocialAccountActionInterface::class, concrete: Actions\CreateSocialAccountAction::class);
    }

    public function boot(): void
    {
        $this->registerMigrations();
        $this->registerConfig();
        $this->registerPayPalProvider();
    }

    private function registerMigrations(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            paths: [__DIR__ . '/../database/migrations/social' => database_path('migrations')],
            groups: 'auth-kit-social-migrations'
        );
    }

    private function registerConfig(): void
    {
        // Merge auth-kit social provider configs into Laravel's services config
        foreach (config('auth-kit.social', []) as $provider => $providerConfig) {
            if (is_array($providerConfig)) {
                config()->set("services.{$provider}", $providerConfig);
            }
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            paths: [__DIR__ . '/../config/auth-kit.php' => config_path('auth-kit.php')],
            groups: 'auth-kit-config'
        );
    }

    private function registerPayPalProvider(): void
    {
        Event::listen(
            events: \SocialiteProviders\Manager\SocialiteWasCalled::class,
            listener: function (\SocialiteProviders\Manager\SocialiteWasCalled $event): void {
                $event->extendSocialite(
                    providerName: 'paypal',
                    providerClass: Services\PayPalSocialProvider::class,
                );
            },
        );
    }
}
