<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Simtabi\Laranail\Auth\Actions\CreateNewUser;

it(description: 'creates a user with a hashed password', closure: function (): void {
    $user = app(CreateNewUser::class)->create([
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ADA@EXAMPLE.COM',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user->email)->toBe('ada@example.com')
        ->and($user->password)->not->toBe('password');
});

it(description: 'fails validation for duplicate email addresses', closure: function (): void {
    User::factory()->create(['email' => 'ada@example.com']);

    $validator = Validator::make(data: [
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ], rules: [
        'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});

it(description: 'fails validation when password confirmation does not match', closure: function (): void {
    $validator = Validator::make(data: [
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada@example.com',
        'password'              => 'password',
        'password_confirmation' => 'different',
    ], rules: [
        'password' => ['required', 'string', Password::default(), 'confirmed'],
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});

it(description: 'fails validation when name is missing', closure: function (): void {
    $validator = Validator::make(data: [
        'name'                  => '',
        'email'                 => 'ada@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ], rules: [
        'name' => ['required', 'string', 'max:255'],
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue();
});

it(description: 'fails validation when email is invalid', closure: function (): void {
    $validator = Validator::make(data: [
        'name'                  => 'Ada Lovelace',
        'email'                 => 'not-an-email',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ], rules: [
        'email' => ['required', 'string', 'email', 'max:255'],
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});

it(description: 'fails validation when password is missing', closure: function (): void {
    $validator = Validator::make(data: [
        'name'  => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ], rules: [
        'password' => ['required', 'string', Password::default(), 'confirmed'],
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});

it(description: 'resolves model from guard config when user_model is not set', closure: function (): void {
    config()->set(key: 'auth-kit.user_model', value: null);
    config()->set(key: 'auth-kit.guard', value: 'web');

    $user = app(CreateNewUser::class)->create([
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user)->toBeInstanceOf(User::class);
});

it(description: 'resolves model from explicit user_model config', closure: function (): void {
    config()->set(key: 'auth-kit.user_model', value: User::class);

    $user = app(CreateNewUser::class)->create([
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada2@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user)->toBeInstanceOf(User::class);
});
