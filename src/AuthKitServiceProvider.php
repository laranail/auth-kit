<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class AuthKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(path: __DIR__ . '/../config/auth-kit.php', key: 'auth-kit');

        $this->app->bind(abstract: CreatesNewUsers::class, concrete: Actions\CreateNewUser::class);
        $this->app->bind(abstract: ResetsUserPasswords::class, concrete: Actions\ResetUserPassword::class);

        $this->app->bind(abstract: Contracts\AttemptEmailPasswordLoginInterface::class, concrete: Actions\AttemptEmailPasswordLogin::class);
        $this->app->bind(abstract: Contracts\CheckEmailExistsInterface::class, concrete: Actions\CheckEmailExists::class);
        $this->app->bind(abstract: Contracts\FindUserByEmailInterface::class, concrete: Actions\FindUserByEmail::class);
        $this->app->bind(abstract: Contracts\LoginUserInterface::class, concrete: Actions\LoginUser::class);
        $this->app->bind(abstract: Contracts\LogoutUserInterface::class, concrete: Actions\LogoutUser::class);
        $this->app->bind(abstract: Contracts\IssueTokenForUserInterface::class, concrete: Actions\IssueTokenForUser::class);

        $this->app->bind(abstract: Contracts\SocialRedirectActionInterface::class, concrete: Actions\SocialRedirectAction::class);
        $this->app->bind(abstract: Contracts\SocialCallbackActionInterface::class, concrete: Actions\SocialCallbackAction::class);
        $this->app->bind(abstract: Contracts\CreateSocialAccountActionInterface::class, concrete: Actions\CreateSocialAccountAction::class);
    }

    public function boot(): void
    {
        $this->configureFortify();
        $this->registerMigrations();
        $this->registerConfig();
        $this->registerPayPalProvider();
    }

    private function configureFortify(): void
    {
        config()->set(key: 'fortify.guard', value: config(key: 'auth-kit.guard', default: 'web'));
        config()->set(key: 'fortify.views', value: config(key: 'auth-kit.fortify.views', default: false));
        config()->set(key: 'fortify.features', value: config(key: 'auth-kit.fortify.features', default: []));
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
        foreach (config(key: 'auth-kit.social', default: []) as $provider => $providerConfig) {
            if (is_array($providerConfig)) {
                config()->set(key: "services.{$provider}", value: $providerConfig);
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
