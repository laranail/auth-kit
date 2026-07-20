<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Actions\LoginUser;

it(description: 'logs the user into the guard', closure: function () {
    $user = User::factory()->create();

    app(abstract: LoginUser::class)->execute(user: $user);

    expect(value: auth()->check())->toBeTrue()
        ->and(value: auth()->id())->toBe(expected: $user->getAuthIdentifier());
});
