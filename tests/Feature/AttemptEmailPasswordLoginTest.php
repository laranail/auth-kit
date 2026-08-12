<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Enums\AuthStatus;
use Simtabi\Laranail\Auth\Actions\AttemptEmailPasswordLogin;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;

it('returns passed when credentials are valid', function (): void {
    $user = User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);

    $result = $action->execute(new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'secret',
        guard: 'web',
    ));

    expect($result->isPassed())->toBeTrue()
        ->and($result->user?->getAuthIdentifier())->toBe($user->getAuthIdentifier());
});

it('returns failed when credentials are wrong', function (): void {
    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);

    $result = $action->execute(new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'wrong',
        guard: 'web',
    ));

    expect($result->isPassed())->toBeFalse();
});

it('throttles repeated failed credentials', function (): void {
    config()->set('auth-kit.rate_limit.max_attempts', 1);

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);
    $input = new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'wrong',
        guard: 'web',
    );

    $action->execute($input);
    $result = $action->execute($input);

    expect($result->status)->toBe(AuthStatus::Throttled);
});

it('throttles per ip address', function (): void {
    config()->set('auth-kit.rate_limit.max_attempts', 1);

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);

    $action->execute(new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'wrong',
        guard: 'web',
        ip: '10.0.0.1',
    ));

    $result = $action->execute(new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'wrong',
        guard: 'web',
        ip: '10.0.0.2',
    ));

    expect($result->isPassed())->toBeFalse()
        ->and($result->status)->not->toBe(AuthStatus::Throttled);
});

it('throttles same ip with same email', function (): void {
    config()->set('auth-kit.rate_limit.max_attempts', 1);

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);

    $action->execute(new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'wrong',
        guard: 'web',
        ip: '10.0.0.1',
    ));

    $result = $action->execute(new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'wrong',
        guard: 'web',
        ip: '10.0.0.1',
    ));

    expect($result->status)->toBe(AuthStatus::Throttled);
});

it('clears the throttle limit on successful login', function (): void {
    config()->set('auth-kit.rate_limit.max_attempts', 2);

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);

    $action->execute(new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'wrong',
        guard: 'web',
    ));

    $result = $action->execute(new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'secret',
        guard: 'web',
    ));

    expect($result->isPassed())->toBeTrue();

    $afterResult = $action->execute(new AttemptEmailPasswordLoginInput(
        email: 'ada@example.com',
        password: 'wrong',
        guard: 'web',
    ));

    expect($afterResult->isPassed())->toBeFalse()
        ->and($afterResult->status)->not->toBe(AuthStatus::Throttled);
});
