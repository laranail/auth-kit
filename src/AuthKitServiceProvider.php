<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Illuminate\Support\ServiceProvider;

class AuthKitServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
    }

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
     * Register the package's publishable resources.
     */
    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/auth-kit.php' => config_path('auth-kit.php'),
        ], 'auth-kit-config');

        $this->publishes([
            __DIR__ . '/../stubs/' => base_path('auth-kit-stubs'),
        ], 'auth-kit-stubs');
    }

    /**
     * Register the package commands.
     */
    private function registerCommands(): void
    {
        $this->commands([
            \Simtabi\Laranail\Auth\Commands\InitAuthCommand::class,
        ]);
    }
}
