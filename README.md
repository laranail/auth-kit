# Laravel Auth Kit

Headless authentication for Laravel 13+. No views, routes, or controllers.

- **Fortify-backed** — password reset, profile updates, password updates, email verification, login throttling
- **Sanctum-ready** — API token issuance via `IssueTokenForUser`
- **Social login** — Google, Facebook, X, LinkedIn, PayPal via Socialite
- **Composable** — separate actions for credential check vs session login

## Requirements

PHP 8.4+ / Laravel 13.x

## Installation

```bash
composer require laranail/auth-kit
php artisan vendor:publish --tag=auth-kit-config
```

For a ready-made Blade UI, install `laranail/auth-preset` instead.

## Configuration

`.env`:

```env
AUTH_KIT_GUARD=web
AUTH_KIT_RATE_LIMIT_MAX_ATTEMPTS=5
AUTH_KIT_RATE_LIMIT_DECAY_MINUTES=1

AUTH_KIT_GOOGLE_CLIENT_ID=
AUTH_KIT_GOOGLE_CLIENT_SECRET=
AUTH_KIT_GOOGLE_REDIRECT=${APP_URL}/auth/google/callback
```

`config/auth-kit.php`:

```php
return [
    'guard' => env('AUTH_KIT_GUARD', 'web'),

    'rate_limit' => [
        'max_attempts'  => (int) env('AUTH_KIT_RATE_LIMIT_MAX_ATTEMPTS', 5),
        'decay_minutes' => (int) env('AUTH_KIT_RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    'fortify' => [
        'views'    => false,
        'features' => ['reset-passwords', 'update-profile-information', 'update-passwords', 'email-verification'],
    ],

    'social' => [
        'google' => [
            'client_id'     => env('AUTH_KIT_GOOGLE_CLIENT_ID'),
            'client_secret' => env('AUTH_KIT_GOOGLE_CLIENT_SECRET'),
            'redirect'      => env('AUTH_KIT_GOOGLE_REDIRECT'),
            'scopes'        => ['openid', 'profile', 'email'],
        ],
        // facebook, twitter, linkedin, paypal ...
    ],
];
```

## Actions

| Action                      | Purpose                                                        |
|-----------------------------|----------------------------------------------------------------|
| `AttemptEmailPasswordLogin` | Verify email + password against a guard, returns `AuthResult`  |
| `LoginUser`                 | Log user into session + regenerate session                     |
| `LogoutUser`                | Log out + invalidate session                                   |
| `CreateNewUser`             | Validate and create user (Fortify `CreatesNewUsers`)           |
| `ResetUserPassword`         | Validate and reset password (Fortify `ResetsUserPasswords`)    |
| `UpdateUserProfileInformation` | Validate and update profile (Fortify `UpdatesUserProfileInformation`) |
| `UpdateUserPassword`         | Validate and update password (Fortify `UpdatesUserPasswords`)  |
| `IssueTokenForUser`         | Issue Sanctum personal access token, returns `TokenResult`     |
| `CheckEmailExists`          | Check if email is registered                                   |
| `FindUserByEmail`           | Retrieve user by email                                         |
| `ResolveSocialIdentity`     | Safe social identity → user resolution (verified-email check)  |
| `SocialRedirectAction`      | Generate OAuth redirect URL, returns `SocialRedirectResult`    |
| `SocialCallbackAction`      | Handle OAuth callback via `ResolveSocialIdentity`              |
| `CreateSocialAccountAction` | Create social account record via polymorphic relation          |

## Result types

**`AuthResult`** — returned by login actions:

```php
AuthResult::passed($user)   // credentials valid
AuthResult::failed()        // credentials invalid
AuthResult::throttled($seconds)  // rate limited
```

Check with `$result->isPassed()` or match on `$result->status` (`AuthStatus::Passed|Failed|Throttled`).

**`TokenResult`** — returned by `IssueTokenForUser`:

```php
new TokenResult(user: $user, token: $token)
```

**`SocialRedirectResult`** — returned by `SocialRedirectAction`:

```php
new SocialRedirectResult(url: $url)
```

## Abstract controllers

Extend these to wire up your own routes. JSON responses are handled automatically.

| Controller                                    | Overridable methods                   |
|-----------------------------------------------|---------------------------------------|
| `AbstractAttemptEmailPasswordLoginController` | `passed()`, `failed()`, `throttled()` |
| `AbstractCheckEmailExistsController`          | `respond()`                           |
| `AbstractLogoutController`                    | `loggedOut()`                         |
| `AbstractRegisterController`                  | `registered()`                        |
| `AbstractSocialRedirectController`            | `redirect()`                          |
| `AbstractSocialCallbackController`            | `passed()`, `failed()`                    |

## Social login

`ResolveSocialIdentity` implements verified-email linking to prevent account takeover:

1. Existing social account → returns user (updates tokens)
2. Authenticated user → links social account
3. Unverified email match → **returns null** (prevents takeover)
4. Verified email match → auto-links
5. No match → creates new user + social record

### Social model

Add to your `User` model:

```php
use Simtabi\Laranail\Auth\Models\Social;
use Illuminate\Database\Eloquent\Relations\MorphMany;

public function socials(): MorphMany
{
    return $this->morphMany(Social::class, 'socialable');
}
```

Publish the migration:

```bash
php artisan vendor:publish --tag=auth-kit-social-migrations
```

## Usage

### Session login (web)

```php
$result = app(AttemptEmailPasswordLogin::class)->execute(
    request: $request,
    guard: 'web',
);

if (! $result->isPassed()) {
    return back()->withErrors(['email' => 'Invalid credentials']);
}

app(LoginUser::class)->execute($result->user, guard: 'web');
return redirect()->intended('/dashboard');
```

### API token

```php
$tokenResult = app(IssueTokenForUser::class)->execute(
    user: $user,
    name: 'api-token',
);

return response()->json([
    'token' => $tokenResult->token,
    'user'  => $tokenResult->user,
]);
```

### Social redirect + callback

```php
$redirect = app(SocialRedirectAction::class)->execute(
    request: $request,
);

return redirect($redirect->url);

// Callback:
$result = app(SocialCallbackAction::class)->execute(
    request: $request,
    guard: 'web',
);
```

## Related packages

- `laranail/auth-preset` — Blade views + routes for this package

## License

MIT
