<?php

declare(strict_types=1);

use Simtabi\Laranail\Auth\Enums\AuthStatus;
use Simtabi\Laranail\Auth\Dtos\EnforceLoginRateLimitInput;
use Simtabi\Laranail\Auth\Actions\EnforceLoginRateLimitAction;

it(description: 'returns allowed when under rate limit', closure: function () {
    $action = app(EnforceLoginRateLimitAction::class);

    $result = $action->execute(new EnforceLoginRateLimitInput(
        key: 'ada@example.com',
        guard: 'web',
    ));

    expect($result->status)->toBe(AuthStatus::Passed)
        ->and($result->user)->toBeNull()
        ->and($result->retryAfterSeconds)->toBeNull();
});

it(description: 'returns throttled when rate limit is exceeded', closure: function () {
    config(['auth-kit.rate_limit.max_attempts' => 2]);
    config(['auth-kit.rate_limit.decay_minutes' => 60]);

    $action = app(EnforceLoginRateLimitAction::class);
    $input = new EnforceLoginRateLimitInput(
        key: 'ada@example.com',
        guard: 'web',
    );

    $action->execute($input);
    $action->execute($input);
    $result = $action->execute($input);

    expect($result->status)->toBe(AuthStatus::Throttled)
        ->and($result->retryAfterSeconds)->toBeGreaterThan(0);
});

it(description: 'uses distinct keys per guard', closure: function () {
    config(['auth-kit.rate_limit.max_attempts' => 1]);
    config(['auth-kit.rate_limit.decay_minutes' => 60]);

    $action = app(EnforceLoginRateLimitAction::class);

    // First call passes (0 → 1 attempt), second call is throttled (1 >= 1)
    $action->execute(new EnforceLoginRateLimitInput(
        key: 'ada@example.com',
        guard: 'web',
    ));

    $webResult = $action->execute(new EnforceLoginRateLimitInput(
        key: 'ada@example.com',
        guard: 'web',
    ));

    // Different guard = different key, so this should still be allowed
    $adminResult = $action->execute(new EnforceLoginRateLimitInput(
        key: 'ada@example.com',
        guard: 'admin',
    ));

    expect($webResult->status)->toBe(AuthStatus::Throttled)
        ->and($adminResult->status)->toBe(AuthStatus::Passed);
});
