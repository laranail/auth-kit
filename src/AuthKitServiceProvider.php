<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\Auth\Guards\LaranailGuard;
use Simtabi\Laranail\Auth\Methods\SocialLoginMethod;
use Simtabi\Laranail\Auth\Methods\EmailPasswordLoginMethod;
use Simtabi\Laranail\Auth\Methods\UsernamePasswordLoginMethod;

class AuthKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/auth-kit.php',
            'auth-kit'
        );

        $this->registerAuthManager();
        $this->registerMethods();
        $this->registerGuard();
    }

    public function boot(): void
    {
        $this->registerPublishables();
        $this->registerMigrations();
    }

    protected function registerAuthManager(): void
    {
        $this->app->singleton(AuthManager::class, function ($app) {
            $manager = new AuthManager(
                $app['auth'],
                $app['request'],
            );

            $manager->registerMethod('email', EmailPasswordLoginMethod::class);
            $manager->registerMethod('username', UsernamePasswordLoginMethod::class);
            $manager->registerMethod('social', SocialLoginMethod::class);

            return $manager;
        });
    }

    protected function registerMethods(): void
    {
        $this->app->scoped(
            abstract: EmailPasswordLoginMethod::class,
            concrete: fn () => new EmailPasswordLoginMethod(),
        );

        $this->app->scoped(
            abstract: UsernamePasswordLoginMethod::class,
            concrete: fn () => new UsernamePasswordLoginMethod(),
        );

        $this->app->scoped(
            abstract: SocialLoginMethod::class,
            concrete: fn () => new SocialLoginMethod(),
        );
    }

    protected function registerGuard(): void
    {
        $authManager = $this->app->make(AuthManager::class);

        $this->app['auth']->extend('laranail', function ($app, $name, array $config) use ($authManager) {
            $provider = $app['auth']->createUserProvider($config['provider'] ?? null);

            return new LaranailGuard(
                $name,
                $provider,
                $app['session.store'],
                $app['request'],
                $authManager,
            );
        });
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(paths: [
            __DIR__.'/../config/auth-kit.php' => config_path('auth-kit.php'),
        ], groups: 'auth-kit-config');

        $this->publishes(paths: [
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], groups: 'auth-kit-migrations');
    }

    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }
}
