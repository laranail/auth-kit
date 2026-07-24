<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Illuminate\Support\ServiceProvider;

class AuthKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(path: __DIR__ . '/../config/auth-kit.php', key: 'auth-kit');

        $this->app->bind(abstract: Contracts\AttemptEmailPasswordLoginInterface::class, concrete: Actions\AttemptEmailPasswordLogin::class);
        $this->app->bind(abstract: Contracts\CheckEmailExistsInterface::class, concrete: Actions\CheckEmailExists::class);
        $this->app->bind(abstract: Contracts\FindUserByEmailInterface::class, concrete: Actions\FindUserByEmail::class);
        $this->app->bind(abstract: Contracts\LoginUserInterface::class, concrete: Actions\LoginUser::class);

        $this->app->bind(abstract: Contracts\AttemptUsernameLoginInterface::class, concrete: Actions\AttemptUsernameLogin::class);
        $this->app->bind(abstract: Contracts\FindUserByUsernameInterface::class, concrete: Actions\FindUserByUsername::class);
        $this->app->bind(abstract: Contracts\CheckUsernameExistsInterface::class, concrete: Actions\CheckUsernameExists::class);

        $this->app->bind(abstract: Contracts\EnforceLoginRateLimitInterface::class, concrete: Actions\EnforceLoginRateLimitAction::class);
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            paths: [__DIR__ . '/../config/auth-kit.php' => config_path('auth-kit.php')],
            groups: 'auth-kit-config'
        );
    }
}
