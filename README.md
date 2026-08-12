# Laravel Auth Kit

A headless authentication toolkit for Laravel 13+, powered by Fortify and Sanctum.

No views. No routes. No controllers shipped. No frontend assumptions.

## Why

Most Laravel auth packages ship UI, routes, and opinions. Auth-kit ships **plain PHP objects** built on top of Fortify's audited security primitives:

- **Fortify contracts** — `CreateNewUser` implements `CreatesNewUsers`, `ResetUserPassword` implements `ResetsUserPasswords`. Same patterns as Fortify stubs.
- **Actions** — single-purpose, injectable, mockable. Login, logout, social identity resolution, token issuance.
- **DTOs** — typed inputs. No arrays across boundaries (except where Fortify's contracts require them).
- **Result objects** — `AuthResult` with named constructors: `passed()`, `failed()`, `throttled()`.
- **Sanctum tokens** — `IssueTokenForUser` issues personal access tokens for API consumers.
- **Composable** — credential verification and session login are separate actions. API callers skip session concerns; web callers chain them.

This makes the package reusable across multiple modules of the same app — e.g. `user` and `admin` modules — each with its own guard.

## Requirements

| Version | PHP            | Laravel |
|---------|----------------|---------|
| 1.x     | 8.4.x, 8.5.x   | 13.x    |

## Installation

```bash
composer require laranail/auth-kit
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag=auth-kit-config
```

### With the Blade preset

```bash
composer require laranail/auth-preset
php artisan laranail:authkit:install
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

    'fortify' => [
        'views'    => false,
        'features' => ['reset-passwords', 'email-verification'],
    ],
];
```

Guards and user providers are defined the standard Laravel way in `config/auth.php`. This package delegates to whatever guards you configure.

## What Fortify provides

Auth-kit configures Fortify under the hood. You get for free:

- Login throttling (per email + IP)
- Password reset (forgot/reset flow)
- Email verification
- Password confirmation
- Two-factor authentication (when enabled)
- Passkeys / WebAuthn (when enabled)

## Available actions

| Action                      | Description                                                             |
|-----------------------------|-------------------------------------------------------------------------|
| `AttemptEmailPasswordLogin` | Verify email + password against a guard, with IP-aware rate limiting    |
| `CheckEmailExists`          | Check whether an email address is registered                            |
| `FindUserByEmail`           | Retrieve a user by email                                                |
| `CreateNewUser`             | Validate and create a user (implements Fortify's `CreatesNewUsers`)     |
| `ResetUserPassword`         | Validate and reset a password (implements Fortify's `ResetsUserPasswords`) |
| `LoginUser`                 | Log an `Authenticatable` into the guard and regenerate the session      |
| `LogoutUser`                | Log the current user out and invalidate the session                     |
| `IssueTokenForUser`         | Issue a Sanctum personal access token                                   |
| `ResolveSocialIdentity`     | Resolve a social identity to a user (safe email verification check)     |
| `SocialRedirectAction`      | Generate OAuth redirect URL for a social provider                       |
| `SocialCallbackAction`      | Handle OAuth callback via `ResolveSocialIdentity`                       |
| `CreateSocialAccountAction` | Create a social account record via polymorphic relation                 |

### Supporting types

| Type                                      | Description                               |
|-------------------------------------------|-------------------------------------------|
| `AuthResult` + `AuthStatus`               | Passed / failed / throttled result object  |
| `TokenResult`                             | User + plain text token from Sanctum       |
| Abstract controllers                      | Thin base classes for the preset to extend |

## Frontend presets

| Preset  | Status      |
|---------|-------------|
| Blade   | Available   |
| React   | Roadmap     |
| Vue     | Roadmap     |
| Livewire| Roadmap     |

## License

MIT
