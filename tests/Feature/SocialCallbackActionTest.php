<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Simtabi\Laranail\Auth\Actions\SocialCallbackAction;
use Simtabi\Laranail\Auth\Dtos\SocialCallbackActionInput;

beforeEach(closure: function (): void {
    $this->socialiteUser = new SocialiteUser();

    $this->socialiteUser->map(attributes: [
        'id'       => '123456789',
        'name'     => 'John Doe',
        'nickname' => 'johndoe',
        'email'    => 'john@example.com',
        'avatar'   => 'https://example.com/avatar.jpg',
    ]);
    $this->socialiteUser->token = 'mock-token';
    $this->socialiteUser->refreshToken = 'mock-refresh-token';
    $this->socialiteUser->expiresIn = 3600;
});

it(description: 'returns passed when closure returns a user', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value, user: $this->socialiteUser);

    $user = User::factory()->create();

    $action = app(abstract: SocialCallbackAction::class);

    $result = $action->execute(input: new SocialCallbackActionInput(
        provider: SocialProvider::GOOGLE,
        resolve: fn () => $user,
    ));

    expect(value: $result->isPassed())->toBeTrue()
        ->and(value: $result->user?->getAuthIdentifier())->toBe($user->getAuthIdentifier());
});

it(description: 'returns failed when closure returns null', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value, user: $this->socialiteUser);

    $action = app(abstract: SocialCallbackAction::class);

    $result = $action->execute(input: new SocialCallbackActionInput(
        provider: SocialProvider::GOOGLE,
        resolve: fn () => null,
    ));

    expect(value: $result->isPassed())->toBeFalse();
});

it(description: 'passes socialite user to closure', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value, user: $this->socialiteUser);

    $action = app(abstract: SocialCallbackAction::class);
    $capturedUser = null;

    $action->execute(input: new SocialCallbackActionInput(
        provider: SocialProvider::GOOGLE,
        resolve: function (SocialiteUser $socialUser) use (&$capturedUser): null {
            $capturedUser = $socialUser;

            return null;
        },
    ));

    expect(value: $capturedUser)->not->toBeNull()
        ->and(value: $capturedUser->getId())->toBe('123456789')
        ->and(value: $capturedUser->getEmail())->toBe('john@example.com')
        ->and(value: $capturedUser->getName())->toBe('John Doe');
});

it(description: 'works with facebook provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::FACEBOOK->value, user: $this->socialiteUser);

    $user = User::factory()->create();

    $action = app(abstract: SocialCallbackAction::class);

    $result = $action->execute(input: new SocialCallbackActionInput(
        provider: SocialProvider::FACEBOOK,
        resolve: fn () => $user,
    ));

    expect(value: $result->isPassed())->toBeTrue();
});

it(description: 'works with paypal provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::PAYPAL->value, user: $this->socialiteUser);

    $user = User::factory()->create();

    $action = app(abstract: SocialCallbackAction::class);

    $result = $action->execute(input: new SocialCallbackActionInput(
        provider: SocialProvider::PAYPAL,
        resolve: fn () => $user,
    ));

    expect(value: $result->isPassed())->toBeTrue();
});
