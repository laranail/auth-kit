<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Simtabi\Laranail\Auth\AuthKitServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AuthKitServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'                  => 'sqlite',
            'database'                => ':memory:',
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__) . '/vendor/orchestra/testbench-core/laravel/migrations');
    }
}
