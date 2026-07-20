<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Actions\CheckEmailExists;
use Simtabi\Laranail\Auth\Dtos\CheckEmailExistsInput;

it(description: 'returns true when the email exists', closure: function (): void {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $action = app(CheckEmailExists::class);
    $input = new CheckEmailExistsInput(email: 'existing@example.com', guard: 'web');

    expect($action->execute($input))->toBeTrue();
});

it(description: 'returns false when the email does not exist', closure: function (): void {
    $action = app(CheckEmailExists::class);
    $input = new CheckEmailExistsInput(email: 'nobody@example.com', guard: 'web');

    expect($action->execute($input))->toBeFalse();
});

it(description: 'respects a custom guard', closure: function (): void {
    User::factory()->create([
        'email' => 'guardtest@example.com',
    ]);

    $action = app(CheckEmailExists::class);
    $input = new CheckEmailExistsInput(
        email: 'guardtest@example.com',
        guard: config('auth-kit.guard'),
    );

    expect($action->execute($input))->toBeTrue();
});
