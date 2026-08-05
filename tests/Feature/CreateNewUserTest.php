<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Simtabi\Laranail\Auth\Actions\CreateNewUser;
use Simtabi\Laranail\Auth\Dtos\CreateNewUserInput;

it('creates a user with a hashed password and dispatches the registered event', function (): void {
    Event::fake();

    $user = app(CreateNewUser::class)->execute(new CreateNewUserInput(
        name: 'Ada Lovelace',
        email: 'ADA@EXAMPLE.COM',
        password: 'password',
        passwordConfirmation: 'password',
        guard: 'web',
    ));

    expect($user->email)->toBe('ada@example.com')
        ->and($user->password)->not->toBe('password');

    Event::assertDispatched(\Illuminate\Auth\Events\Registered::class);
});

it('rejects duplicate email addresses', function (): void {
    User::factory()->create(['email' => 'ada@example.com']);

    app(CreateNewUser::class)->execute(new CreateNewUserInput(
        name: 'Ada Lovelace',
        email: 'ada@example.com',
        password: 'password',
        passwordConfirmation: 'password',
        guard: 'web',
    ));
})->throws(ValidationException::class);
