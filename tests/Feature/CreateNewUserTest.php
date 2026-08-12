<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Validation\ValidationException;
use Simtabi\Laranail\Auth\Actions\CreateNewUser;

it('creates a user with a hashed password', function (): void {
    $user = app(CreateNewUser::class)->create([
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ADA@EXAMPLE.COM',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user->email)->toBe('ada@example.com')
        ->and($user->password)->not->toBe('password');
});

it('rejects duplicate email addresses', function (): void {
    User::factory()->create(['email' => 'ada@example.com']);

    app(CreateNewUser::class)->create([
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);
})->throws(ValidationException::class);
