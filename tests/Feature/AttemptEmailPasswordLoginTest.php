<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Enums\AuthStatus;
use Simtabi\Laranail\Auth\Actions\AttemptEmailPasswordLogin;

function loginRequest(array $data = [], ?string $ip = null): Request
{
    $request = Request::create(uri: '/login', method: 'POST', parameters: $data);

    if ($ip !== null) {
        $request->server->set('REMOTE_ADDR', $ip);
        $request->headers->set('X-Forwarded-For', $ip);
    }

    return $request;
}

it('returns passed when credentials are valid', function (): void {
    $user = User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);

    $result = $action->execute(
        request: loginRequest(['email' => 'ada@example.com', 'password' => 'secret']),
        guard: 'web',
    );

    expect($result->isPassed())->toBeTrue()
        ->and($result->user?->getAuthIdentifier())->toBe($user->getAuthIdentifier());
});

it('returns failed when credentials are wrong', function (): void {
    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);

    $result = $action->execute(
        request: loginRequest(['email' => 'ada@example.com', 'password' => 'wrong']),
        guard: 'web',
    );

    expect($result->isPassed())->toBeFalse();
});

it('throttles repeated failed credentials', function (): void {
    config()->set('auth-kit.rate_limit.max_attempts', 1);

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);
    $request = loginRequest(['email' => 'ada@example.com', 'password' => 'wrong']);

    $action->execute(request: $request, guard: 'web');
    $result = $action->execute(request: $request, guard: 'web');

    expect($result->status)->toBe(AuthStatus::Throttled);
});

it('throttles per ip address', function (): void {
    config()->set('auth-kit.rate_limit.max_attempts', 1);

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);

    $action->execute(
        request: loginRequest(['email' => 'ada@example.com', 'password' => 'wrong'], ip: '10.0.0.1'),
        guard: 'web',
    );

    $result = $action->execute(
        request: loginRequest(['email' => 'ada@example.com', 'password' => 'wrong'], ip: '10.0.0.2'),
        guard: 'web',
    );

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

    $action->execute(
        request: loginRequest(['email' => 'ada@example.com', 'password' => 'wrong'], ip: '10.0.0.1'),
        guard: 'web',
    );

    $result = $action->execute(
        request: loginRequest(['email' => 'ada@example.com', 'password' => 'wrong'], ip: '10.0.0.1'),
        guard: 'web',
    );

    expect($result->status)->toBe(AuthStatus::Throttled);
});

it('clears the throttle limit on successful login', function (): void {
    config()->set('auth-kit.rate_limit.max_attempts', 2);

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptEmailPasswordLogin::class);

    $action->execute(
        request: loginRequest(['email' => 'ada@example.com', 'password' => 'wrong']),
        guard: 'web',
    );

    $result = $action->execute(
        request: loginRequest(['email' => 'ada@example.com', 'password' => 'secret']),
        guard: 'web',
    );

    expect($result->isPassed())->toBeTrue();

    $afterResult = $action->execute(
        request: loginRequest(['email' => 'ada@example.com', 'password' => 'wrong']),
        guard: 'web',
    );

    expect($afterResult->isPassed())->toBeFalse()
        ->and($afterResult->status)->not->toBe(AuthStatus::Throttled);
});
