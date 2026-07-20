<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Actions\FindUserByEmail;
use Simtabi\Laranail\Auth\Dtos\FindUserByEmailInput;

it(description: 'returns user when the email exists', closure: function (): void {
    $user = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $action = app(FindUserByEmail::class);
    $input = new FindUserByEmailInput(email: 'existing@example.com', guard: 'web');

    $found = $action->execute($input);

    expect($found)->not->toBeNull()
        ->and($found->email)->toBe('existing@example.com');
});

it(description: 'returns null when the email does not exist', closure: function (): void {
    $action = app(FindUserByEmail::class);
    $input = new FindUserByEmailInput(email: 'nobody@example.com', guard: 'web');

    expect($action->execute($input))->toBeNull();
});

it(description: 'respects a custom guard', closure: function (): void {
    $user = User::factory()->create([
        'email' => 'guardtest@example.com',
    ]);

    $action = app(FindUserByEmail::class);
    $input = new FindUserByEmailInput(
        email: 'guardtest@example.com',
        guard: config('auth-kit.guard'),
    );

    $found = $action->execute($input);

    expect($found)->not->toBeNull()
        ->and($found->email)->toBe('guardtest@example.com');
});
