<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Illuminate\Support\ServiceProvider;

class AuthKitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../config/auth-kit.php',
            key: 'auth-kit'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            paths: [__DIR__ . '/../config/auth-kit.php' => config_path(path: 'auth-kit.php')],
            groups: 'auth-kit-config'
        );
    }
}
