<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Actions\AttemptEmailPasswordLogin;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;

it(description: 'returns passed when credentials are valid', closure: function () {
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

it(description: 'returns failed when credentials are wrong', closure: function () {
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
