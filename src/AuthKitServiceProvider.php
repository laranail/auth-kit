<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Passkeys;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Auth\Models\Passkey;

class AuthKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(path: __DIR__ . '/../config/auth-kit.php', key: 'auth-kit');

        Passkeys::usePasskeyModel(Passkey::class);

        $this->app->bind(abstract: Contracts\AttemptEmailPasswordLoginInterface::class, concrete: Actions\AttemptEmailPasswordLogin::class);
        $this->app->bind(abstract: Contracts\CheckEmailExistsInterface::class, concrete: Actions\CheckEmailExists::class);
        $this->app->bind(abstract: Contracts\FindUserByEmailInterface::class, concrete: Actions\FindUserByEmail::class);
        $this->app->bind(abstract: Contracts\LoginUserInterface::class, concrete: Actions\LoginUser::class);
        $this->app->bind(abstract: Contracts\LogoutUserInterface::class, concrete: Actions\LogoutUser::class);
        $this->app->bind(abstract: Contracts\IssueTokenForUserInterface::class, concrete: Actions\IssueTokenForUser::class);

        $this->app->bind(abstract: Contracts\SocialRedirectActionInterface::class, concrete: Actions\SocialRedirectAction::class);
        $this->app->bind(abstract: Contracts\SocialCallbackActionInterface::class, concrete: Actions\SocialCallbackAction::class);
        $this->app->bind(abstract: Contracts\CreateSocialAccountActionInterface::class, concrete: Actions\CreateSocialAccountAction::class);
        $this->app->bind(abstract: Contracts\ResolveSocialIdentityInterface::class, concrete: Actions\ResolveSocialIdentity::class);
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

        Fortify::createUsersUsing(callback: Actions\CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(callback: Actions\UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(callback: Actions\UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(callback: Actions\ResetUserPassword::class);
    }

    private function registerMigrations(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            paths: [__DIR__ . '/../database/migrations/social' => database_path(path: 'migrations')],
            groups: 'auth-kit-social-migrations'
        );

        $this->publishes(
            paths: [__DIR__ . '/../database/migrations/passkeys' => database_path(path: 'migrations')],
            groups: 'auth-kit-passkey-migrations'
        );
    }

    private function registerConfig(): void
    {
        foreach (config(key: 'auth-kit.social', default: []) as $provider => $providerConfig) {
            if (is_array(value: $providerConfig)) {
                config()->set(key: "services.{$provider}", value: $providerConfig);
            }
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            paths: [__DIR__ . '/../config/auth-kit.php' => config_path(path: 'auth-kit.php')],
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
