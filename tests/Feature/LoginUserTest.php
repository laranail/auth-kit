<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Simtabi\Laranail\Auth\Actions\LoginUser;

uses(RefreshDatabase::class);

it('logs the user into the guard', function () {
    $user = User::factory()->create();

    app(LoginUser::class)->execute($user);

    expect(auth()->check())->toBeTrue()
        ->and(auth()->id())->toBe($user->getAuthIdentifier());
});
