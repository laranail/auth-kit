<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Auth\Contracts\LoginUserInterface;
use Simtabi\Laranail\Auth\Contracts\FindUserByEmailInterface;
use Simtabi\Laranail\Auth\Contracts\CheckEmailExistsInterface;
use Simtabi\Laranail\Auth\Contracts\AttemptEmailPasswordLoginInterface;

class AuthKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/auth-kit.php', 'auth-kit');

        $this->app->bind(AttemptEmailPasswordLoginInterface::class, Actions\AttemptEmailPasswordLogin::class);
        $this->app->bind(CheckEmailExistsInterface::class, Actions\CheckEmailExists::class);
        $this->app->bind(FindUserByEmailInterface::class, Actions\FindUserByEmail::class);
        $this->app->bind(LoginUserInterface::class, Actions\LoginUser::class);
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/auth-kit.php' => config_path('auth-kit.php'),
        ], 'auth-kit-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'auth-kit-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
