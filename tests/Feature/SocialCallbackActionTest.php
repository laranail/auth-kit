<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Simtabi\Laranail\Auth\Models\Social;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Simtabi\Laranail\Auth\Actions\SocialCallbackAction;
use Simtabi\Laranail\Auth\Dtos\SocialCallbackActionInput;

beforeEach(function (): void {
    $this->socialiteUser = new SocialiteUser();
    $this->socialiteUser->map([
        'id'             => '123456789',
        'name'           => 'John Doe',
        'nickname'       => 'johndoe',
        'email'          => 'john@example.com',
        'avatar'         => 'https://example.com/avatar.jpg',
        'email_verified' => true,
    ]);
    $this->socialiteUser->token = 'mock-token';
    $this->socialiteUser->refreshToken = 'mock-refresh-token';
    $this->socialiteUser->expiresIn = 3600;
});

it('creates a new user and social account when no match exists', function (): void {
    Socialite::fake(SocialProvider::GOOGLE->value, $this->socialiteUser);

    $result = app(SocialCallbackAction::class)->execute(new SocialCallbackActionInput(
        provider: SocialProvider::GOOGLE,
        guard: 'web',
    ));

    expect($result->isPassed())->toBeTrue()
        ->and($result->user->email)->toBe('john@example.com')
        ->and(Social::query()->count())->toBe(1);
});

it('returns existing user when social account already exists', function (): void {
    Socialite::fake(SocialProvider::GOOGLE->value, $this->socialiteUser);

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

    $result = app(SocialCallbackAction::class)->execute(new SocialCallbackActionInput(
        provider: SocialProvider::GOOGLE,
        guard: 'web',
    ));

    expect($result->isPassed())->toBeTrue()
        ->and($result->user->getAuthIdentifier())->toBe($existingUser->getAuthIdentifier());
});

it('returns failed when socialite user has no email', function (): void {
    $noEmailUser = new SocialiteUser();
    $noEmailUser->map([
        'id'       => '123456789',
        'name'     => 'No Email',
        'nickname' => 'noemail',
    ]);
    $noEmailUser->token = 'mock-token';
    $noEmailUser->refreshToken = 'mock-refresh';

    Socialite::fake(SocialProvider::GOOGLE->value, $noEmailUser);

    $result = app(SocialCallbackAction::class)->execute(new SocialCallbackActionInput(
        provider: SocialProvider::GOOGLE,
        guard: 'web',
    ));

    expect($result->isPassed())->toBeFalse();
});
