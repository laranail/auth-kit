<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Simtabi\Laranail\Auth\Dtos\CheckEmailExistsInput;
use Simtabi\Laranail\Auth\Actions\CheckEmailExistsAction;

uses(RefreshDatabase::class);

it('returns true when the email exists', function (): void {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $action = app(CheckEmailExistsAction::class);
    $input = new CheckEmailExistsInput(email: 'existing@example.com');

    expect($action->execute($input))->toBeTrue();
});

it('returns false when the email does not exist', function (): void {
    $action = app(CheckEmailExistsAction::class);
    $input = new CheckEmailExistsInput(email: 'nobody@example.com');

    expect($action->execute($input))->toBeFalse();
});

it('respects a custom guard', function (): void {
    User::factory()->create([
        'email' => 'guardtest@example.com',
    ]);

    $action = app(CheckEmailExistsAction::class);
    $input = new CheckEmailExistsInput(
        email: 'guardtest@example.com',
        guard: config('auth-kit.guard'),
    );

    expect($action->execute($input))->toBeTrue();
});
