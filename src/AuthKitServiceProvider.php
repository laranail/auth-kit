<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Auth\Actions\EmailPasswordLoginAction;
use Simtabi\Laranail\Auth\Actions\UsernamePasswordLoginAction;

class AuthKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerActions();
    }

    public function boot(): void
    {
        $this->registerPublishables();
        $this->registerMigrations();
    }

    protected function registerActions(): void
    {
        $this->app->scoped(
            abstract: EmailPasswordLoginAction::class,
            concrete: fn () => new EmailPasswordLoginAction(),
        );

        $this->app->scoped(
            abstract: UsernamePasswordLoginAction::class,
            concrete: fn () => new UsernamePasswordLoginAction(),
        );
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(paths: [
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], groups: 'auth-kit-migrations');
    }

    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}
