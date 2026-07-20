<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Simtabi\Laranail\Auth\Actions\AttemptEmailPasswordLogin;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;

uses(RefreshDatabase::class);

it('returns passed when credentials are valid', function () {
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

it('returns failed when credentials are wrong', function () {
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
