<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Laravel\Socialite\Facades\Socialite;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Simtabi\Laranail\Auth\Actions\SocialRedirectAction;

function redirectRequest(string $provider, ?string $state = null): Request
{
    $request = Request::create(uri: "/auth/social/{$provider}", method: 'GET', parameters: $state ? ['state' => $state] : []);
    $request->setRouteResolver(fn () => (new Route('GET', "/auth/social/{provider}", []))->bind($request));
    $request->route()->setParameter('provider', $provider);

    return $request;
}

it(description: 'returns redirect url for valid provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(request: redirectRequest('google'));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'passes state to result', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(request: redirectRequest('google', state: 'test-state-value'));

    expect(value: $result->state)->toBe('test-state-value');
});

it(description: 'works with facebook provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::FACEBOOK->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(request: redirectRequest('facebook'));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'works with twitter provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::TWITTER->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(request: redirectRequest('twitter'));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'works with linkedin provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::LINKEDIN->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(request: redirectRequest('linkedin'));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'works with paypal provider', closure: function (): void {
    Socialite::fake(driver: SocialProvider::PAYPAL->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(request: redirectRequest('paypal'));

    expect(value: $result->url)->toBeString()->not->toBeEmpty();
});

it(description: 'returns null state when not provided', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value);

    $action = app(abstract: SocialRedirectAction::class);

    $result = $action->execute(request: redirectRequest('google'));

    expect(value: $result->state)->toBeNull();
});
