<?php

declare(strict_types=1);

use Simtabi\Laranail\Auth\AuthKit;
use Simtabi\Laranail\Auth\Support\AuthConfig;

test('auth config resolves explicit guard', function () {
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'eloquent']);
    config(['auth.providers.users.model' => \App\Models\User::class]);

    $config = AuthConfig::fromGuard('web');

    expect($config->guard)->toBe('web');
    expect($config->provider)->toBe('users');
    expect($config->model)->toBe(\App\Models\User::class);
});

test('auth config resolves configured default guard', function () {
    config(['auth.defaults.guard' => 'admin']);
    config(['auth.guards.admin.provider' => 'admins']);
    config(['auth.providers.admins.driver' => 'eloquent']);
    config(['auth.providers.admins.model' => \App\Models\User::class]);

    $config = AuthConfig::fromGuard();

    expect($config->guard)->toBe('admin');
});

test('auth config falls back to web', function () {
    config(['auth.defaults.guard' => null]);
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'eloquent']);
    config(['auth.providers.users.model' => \App\Models\User::class]);

    $config = AuthConfig::fromGuard();

    expect($config->guard)->toBe('web');
});

test('auth config throws on missing provider', function () {
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web.provider' => null]);

    AuthConfig::fromGuard('web');
})->throws(InvalidArgumentException::class, 'Auth guard [web] does not define a valid provider.');

test('auth config throws on non-eloquent provider', function () {
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'database']);

    AuthConfig::fromGuard('web');
})->throws(InvalidArgumentException::class, 'Auth provider [users] must use the eloquent driver.');

test('auth config throws on missing model', function () {
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'eloquent']);
    config(['auth.providers.users.model' => null]);

    AuthConfig::fromGuard('web');
})->throws(InvalidArgumentException::class, 'Auth provider [users] does not define a valid model.');

test('auth kit defaults guard to web', function () {
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'eloquent']);
    config(['auth.providers.users.model' => \App\Models\User::class]);

    $kit = new AuthKit();
    expect($kit->getGuard())->toBe('web');
});

test('auth kit allows static guard factory', function () {
    config(['auth.guards.admin.provider' => 'admins']);
    config(['auth.providers.admins.driver' => 'eloquent']);
    config(['auth.providers.admins.model' => \App\Models\User::class]);

    $kit = AuthKit::guard('admin');
    expect($kit->getGuard())->toBe('admin');
});

test('auth kit resolves provider from config', function () {
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'eloquent']);
    config(['auth.providers.users.model' => \App\Models\User::class]);

    $kit = new AuthKit();
    expect($kit->getProvider())->toBe('users');
});

test('auth kit resolves model from config', function () {
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'eloquent']);
    config(['auth.providers.users.model' => \App\Models\User::class]);

    $kit = new AuthKit();
    expect($kit->getModel())->toBe(\App\Models\User::class);
});
