<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Simtabi\Laranail\Auth\Actions\Session\LoginUserAction;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('logs the user into the guard', function () {
    $user = User::factory()->create();

    app(LoginUserAction::class)->execute($user);

    expect(auth()->check())->toBeTrue()
        ->and(auth()->id())->toBe($user->getAuthIdentifier());
});
