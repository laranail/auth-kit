<?php

declare(strict_types=1);

use Simtabi\Laranail\Auth\Results\AuthResult;
use Simtabi\Laranail\Auth\Results\AuthStatus;
use Illuminate\Contracts\Auth\Authenticatable;

it('creates a passed result', function () {
    $user = Mockery::mock(Authenticatable::class);

    $result = AuthResult::passed($user);

    expect($result->isPassed())->toBeTrue()
        ->and($result->status)->toBe(AuthStatus::Passed)
        ->and($result->user)->toBe($user);
});

it('creates a failed result', function () {
    $result = AuthResult::failed();

    expect($result->isPassed())->toBeFalse()
        ->and($result->status)->toBe(AuthStatus::Failed)
        ->and($result->user)->toBeNull();
});

it('creates a throttled result with retry seconds', function () {
    $result = AuthResult::throttled(30);

    expect($result->status)->toBe(AuthStatus::Throttled)
        ->and($result->retryAfterSeconds)->toBe(30);
});
