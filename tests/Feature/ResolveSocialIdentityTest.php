<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Models\Social;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Simtabi\Laranail\Auth\Actions\ResolveSocialIdentity;
use Simtabi\Laranail\Auth\Dtos\ResolveSocialIdentityInput;

function socialiteUser(array $overrides = []): SocialiteUser
{
    $raw = array_merge([
        'id'             => '123456789',
        'name'           => 'John Doe',
        'nickname'       => 'johndoe',
        'email'          => 'john@example.com',
        'avatar'         => 'https://example.com/avatar.jpg',
        'email_verified' => true,
    ], $overrides);

    $user = new SocialiteUser();
    $user->setRaw($raw);
    $user->map($raw);
    $user->token = 'mock-token';
    $user->refreshToken = 'mock-refresh-token';
    $user->expiresIn = 3600;

    return $user;
}

function socialiteUserWithoutVerification(): SocialiteUser
{
    $raw = [
        'id'       => '123456789',
        'name'     => 'John Doe',
        'nickname' => 'johndoe',
        'email'    => 'john@example.com',
        'avatar'   => 'https://example.com/avatar.jpg',
    ];

    $user = new SocialiteUser();
    $user->setRaw($raw);
    $user->map($raw);
    $user->token = 'mock-token';
    $user->refreshToken = 'mock-refresh-token';
    $user->expiresIn = 3600;

    return $user;
}

it('returns existing socialable when social record matches', function (): void {
    $existingUser = User::factory()->create(['email' => 'john@example.com']);
    Social::query()->create([
        'socialable_type' => get_class($existingUser),
        'socialable_id'   => $existingUser->getAuthIdentifier(),
        'provider'        => 'google',
        'provider_id'     => '123456789',
        'name'            => 'John Doe',
        'email'           => 'john@example.com',
        'token'           => 'old-token',
        'refresh_token'   => 'old-refresh',
    ]);

    $user = app(ResolveSocialIdentity::class)->execute(new ResolveSocialIdentityInput(
        provider: SocialProvider::GOOGLE,
        socialUser: socialiteUser(),
        guard: 'web',
    ));

    expect($user->getAuthIdentifier())->toBe($existingUser->getAuthIdentifier());
});

it('updates tokens on existing social record', function (): void {
    $existingUser = User::factory()->create(['email' => 'john@example.com']);
    Social::query()->create([
        'socialable_type' => get_class($existingUser),
        'socialable_id'   => $existingUser->getAuthIdentifier(),
        'provider'        => 'google',
        'provider_id'     => '123456789',
        'name'            => 'John Doe',
        'email'           => 'john@example.com',
        'token'           => 'old-token',
        'refresh_token'   => 'old-refresh',
    ]);

    app(ResolveSocialIdentity::class)->execute(new ResolveSocialIdentityInput(
        provider: SocialProvider::GOOGLE,
        socialUser: socialiteUser(),
        guard: 'web',
    ));

    $social = Social::first();
    expect($social->token)->not->toBe('old-token');
});

it('auto-links by email only when provider asserts email is verified', function (): void {
    $existingUser = User::factory()->create(['email' => 'john@example.com']);

    $user = app(ResolveSocialIdentity::class)->execute(new ResolveSocialIdentityInput(
        provider: SocialProvider::GOOGLE,
        socialUser: socialiteUser(['email_verified' => true]),
        guard: 'web',
    ));

    expect($user->getAuthIdentifier())->toBe($existingUser->getAuthIdentifier())
        ->and(Social::query()->count())->toBe(1);
});

it('does not auto-link by email when provider has not verified it', function (): void {
    User::factory()->create(['email' => 'john@example.com']);

    $user = app(ResolveSocialIdentity::class)->execute(new ResolveSocialIdentityInput(
        provider: SocialProvider::GOOGLE,
        socialUser: socialiteUser(['email_verified' => false]),
        guard: 'web',
    ));

    expect($user)->toBeNull();
});

it('does not auto-link when raw user has no verification field', function (): void {
    User::factory()->create(['email' => 'john@example.com']);

    $user = app(ResolveSocialIdentity::class)->execute(new ResolveSocialIdentityInput(
        provider: SocialProvider::GOOGLE,
        socialUser: socialiteUserWithoutVerification(),
        guard: 'web',
    ));

    expect($user)->toBeNull();
});

it('returns null when socialite user has no email and no social record exists', function (): void {
    $user = app(ResolveSocialIdentity::class)->execute(new ResolveSocialIdentityInput(
        provider: SocialProvider::GOOGLE,
        socialUser: socialiteUser(['email' => null]),
        guard: 'web',
    ));

    expect($user)->toBeNull();
});

it('creates a new user when no match and email is verified', function (): void {
    $user = app(ResolveSocialIdentity::class)->execute(new ResolveSocialIdentityInput(
        provider: SocialProvider::GOOGLE,
        socialUser: socialiteUser(),
        guard: 'web',
    ));

    expect($user)->not->toBeNull()
        ->and($user->email)->toBe('john@example.com')
        ->and(Social::query()->count())->toBe(1)
        ->and(Social::first()->provider->value)->toBe('google');
});

it('links social account to authenticated user', function (): void {
    $authUser = User::factory()->create();
    $this->actingAs($authUser);

    $user = app(ResolveSocialIdentity::class)->execute(new ResolveSocialIdentityInput(
        provider: SocialProvider::GOOGLE,
        socialUser: socialiteUser(),
        guard: 'web',
    ));

    expect($user->getAuthIdentifier())->toBe($authUser->getAuthIdentifier())
        ->and(Social::query()->count())->toBe(1)
        ->and(Social::first()->socialable_id)->toBe($authUser->getAuthIdentifier());
});
