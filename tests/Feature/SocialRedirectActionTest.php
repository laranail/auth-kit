<?php

declare(strict_types=1);

use Laravel\Socialite\Facades\Socialite;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Simtabi\Laranail\Auth\Actions\SocialRedirectAction;
use Simtabi\Laranail\Auth\Dtos\SocialRedirectActionInput;

it(description: 'returns redirect url for valid provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(input: new SocialRedirectActionInput(
        provider: SocialProvider::GOOGLE,
    ));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'passes state to result', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(input: new SocialRedirectActionInput(
        provider: SocialProvider::GOOGLE,
        state: 'test-state-value',
    ));

    expect(value: $result->state)->toBe('test-state-value');
});

it(description: 'works with facebook provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::FACEBOOK->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(input: new SocialRedirectActionInput(
        provider: SocialProvider::FACEBOOK,
    ));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'works with twitter provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::TWITTER->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(input: new SocialRedirectActionInput(
        provider: SocialProvider::TWITTER,
    ));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'works with linkedin provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::LINKEDIN->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(input: new SocialRedirectActionInput(
        provider: SocialProvider::LINKEDIN,
    ));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'works with paypal provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::PAYPAL->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(input: new SocialRedirectActionInput(
        provider: SocialProvider::PAYPAL,
    ));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'returns null state when not provided', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(input: new SocialRedirectActionInput(
        provider: SocialProvider::GOOGLE,
    ));

    expect(value: $result->state)->toBeNull();
});
