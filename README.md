# Laravel Auth Kit

A headless, action-based authentication toolkit for Laravel.

No views. No routes. No controllers shipped. No frontend assumptions.

## Why

Most Laravel auth packages ship UI, routes, and opinions. This one ships **plain PHP objects**:

- **Actions** — one public method: `execute()`. Single-purpose, injectable, mockable.
- **DTOs** — typed `readonly` inputs. No arrays across boundaries.
- **Result objects** — typed outputs. No `bool`, no `?User`.
- **No exceptions** for expected outcomes (wrong password, throttled). Exceptions are reserved for programmer errors.
- **Composable** — credential verification and session login are separate actions. API callers skip session concerns; web callers chain them.

This makes the package reusable across multiple modules of the same app — e.g. `user` and `admin` modules — each with its own guard.

## Requirements

| Version | PHP          | Laravel |
|---------|--------------|---------|
| 1.x     | 8.4.x, 8.5.x | 13.x    |

## Installation

```bash
composer require laranail/auth-kit
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag=auth-kit-config
```

## Configuration

`config/auth-kit.php`:

```php
return [
    'guard' => env('AUTH_KIT_GUARD', 'web'),

    'rate_limit' => [
        'max_attempts'  => (int) env('AUTH_KIT_RATE_LIMIT_MAX_ATTEMPTS', 5),
        'decay_minutes' => (int) env('AUTH_KIT_RATE_LIMIT_DECAY_MINUTES', 1),
    ],
];
```

Guards and user providers are defined the standard Laravel way in `config/auth.php`. This package does not ship a custom guard driver — it delegates to whatever guards you configure.

## Concepts

### Actions

An action is a single-purpose class with one public method:

```php
$action = app(AttemptEmailPasswordLogin::class);

$result = $action->execute(new AttemptEmailPasswordLoginInput(
    email:    'ada@example.com',
    password: 'secret',
    guard:    'web',
));
```

### Result object

`AuthResult` represents the outcome of any authentication attempt:

| Named constructor                 | Status                  | Extra data                       |
|-----------------------------------|-------------------------|----------------------------------|
| `AuthResult::passed($user)`       | `AuthStatus::Passed`    | `user`                           |
| `AuthResult::failed()`            | `AuthStatus::Failed`    | —                                |
| `AuthResult::throttled($seconds)` | `AuthStatus::Throttled` | `retryAfterSeconds`              |
| `AuthResult::allowed()`           | `AuthStatus::Passed`    | — (no user, for pre-auth checks) |

Check the outcome with `->isPassed()` or by switching on `->status`.

### Composition

Actions compose. Verifying credentials and logging the user into the session are **separate actions** so headless callers (API, mobile, queue jobs) can skip session concerns entirely, while session-based callers chain them together.

## Available actions

| Action                        | Description                                                        | Docs                                         |
|-------------------------------|--------------------------------------------------------------------|----------------------------------------------|
| `AttemptEmailPasswordLogin`   | Verify email + password against a guard                            | [Docs](docs/attempt-email-password-login.md) |
| `AttemptUsernameLogin`        | Verify username + password against a guard                         | [Docs](docs/attempt-username-login.md)       |
| `CheckEmailExists`            | Check whether an email address is registered                       | [Docs](docs/check-email-exists.md)           |
| `CheckUsernameExists`         | Check whether a username is registered                             | [Docs](docs/check-username-exists.md)        |
| `FindUserByEmail`             | Retrieve a user by email                                           | [Docs](docs/find-user-by-email.md)           |
| `FindUserByUsername`          | Retrieve a user by username                                        | [Docs](docs/find-user-by-username.md)        |
| `EnforceLoginRateLimitAction` | Check and increment rate limit counter                             | [Docs](docs/enforce-login-rate-limit.md)     |
| `LoginUser`                   | Log an `Authenticatable` into the guard and regenerate the session | [Docs](docs/login-user.md)                   |
| `LogoutUser`                  | Log the current user out and invalidate the session                | [Docs](docs/logout-user.md)                  |

### Supporting types

| Type                        | Docs                                 |
|-----------------------------|--------------------------------------|
| `AuthResult` + `AuthStatus` | [Docs](docs/auth-result.md)          |
| Abstract controllers        | [Docs](docs/abstract-controllers.md) |

## Roadmap

- Social login (redirect + callback actions)
- 2FA / MFA (challenge issue + verify actions with TOTP, OTP, recovery codes)
- Optional HTTP companion package (`laranail/auth-kit-http`) with controllers and route macros

## License

MIT
