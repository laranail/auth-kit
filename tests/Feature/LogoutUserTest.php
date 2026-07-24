<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Actions\LoginUser;
use Simtabi\Laranail\Auth\Actions\LogoutUser;

it(description: 'logs the user out of the guard', closure: function () {
    $user = User::factory()->create();

    app(abstract: LoginUser::class)->execute(user: $user);
    expect(value: auth()->check())->toBeTrue();

    app(abstract: LogoutUser::class)->execute();
    expect(value: auth()->check())->toBeFalse();
});

it(description: 'invalidates the session', closure: function () {
    $user = User::factory()->create();

    app(abstract: LoginUser::class)->execute(user: $user);
    $oldSessionId = session()->getId();

    app(abstract: LogoutUser::class)->execute();

    expect(value: session()->getId())->not->toBe($oldSessionId);
});
