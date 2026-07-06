<?php

declare(strict_types=1);

use Simtabi\Laranail\Auth\Support\AuthConfig;

test(description: 'auth config resolves explicit guard', closure: function () {
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'eloquent']);
    config(['auth.providers.users.model' => \App\Models\User::class]);

    $config = AuthConfig::fromGuard('web');

    expect($config->guard)->toBe('web');
    expect($config->provider)->toBe('users');
    expect($config->model)->toBe(\App\Models\User::class);
});

test(description: 'auth config resolves configured default guard', closure: function () {
    config(['auth.defaults.guard' => 'admin']);
    config(['auth.guards.admin.provider' => 'admins']);
    config(['auth.providers.admins.driver' => 'eloquent']);
    config(['auth.providers.admins.model' => \App\Models\User::class]);

    $config = AuthConfig::fromGuard();

    expect($config->guard)->toBe('admin');
});

test(description: 'auth config falls back to web', closure: function () {
    config(['auth.defaults.guard' => null]);
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'eloquent']);
    config(['auth.providers.users.model' => \App\Models\User::class]);

    $config = AuthConfig::fromGuard();

    expect($config->guard)->toBe('web');
});

test(description: 'auth config throws on missing provider', closure: function () {
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web.provider' => null]);

    AuthConfig::fromGuard('web');
})->throws(exception: InvalidArgumentException::class, exceptionMessage: 'Auth guard [web] does not define a valid provider.');

test(description: 'auth config throws on non-eloquent provider', closure: function () {
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'database']);

    AuthConfig::fromGuard('web');
})->throws(exception: InvalidArgumentException::class, exceptionMessage: 'Auth provider [users] must use the eloquent driver.');

test(description: 'auth config throws on missing model', closure: function () {
    config(['auth.defaults.guard' => 'web']);
    config(['auth.guards.web.provider' => 'users']);
    config(['auth.providers.users.driver' => 'eloquent']);
    config(['auth.providers.users.model' => null]);

    AuthConfig::fromGuard('web');
})->throws(exception: InvalidArgumentException::class, exceptionMessage: 'Auth provider [users] does not define a valid model.');
