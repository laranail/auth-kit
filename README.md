# Laravel Auth Kit

A headless, framework-agnostic Laravel authentication package. No views, no frontend dependencies, no opinionated UI. Just auth logic you can wire into anything — Blade, Livewire, Inertia, API, or custom frontends.

## Features

- **Headless** — no views, no routes, no frontend coupling
- Pluggable auth methods via `AuthMethod` contract
- Rate limiting with configurable thresholds
- Event-driven (success, failure, throttled)
- Multi-guard support
- Custom guard driver

## Requirements

| Version | PHP          | Laravel |
|---------|--------------|---------|
| 1.x     | 8.4.x, 8.5.x | 13.x    |

## Installation

```bash
composer require laranail/auth-kit
```

## Conventions

This package follows Laravel auth conventions:

| Field          | Column           |
|----------------|------------------|
| Email login    | `email`          |
| Username login | `username`       |
| Password       | `password`       |
| Remember token | `remember_token` |

## Guard Resolution

Methods accept an optional guard name.

If no guard is provided, the package resolves the guard from:

1. `auth.defaults.guard`
2. fallback to `web`

The resolved provider and model are read from Laravel's `auth` configuration.

## Architecture

### AuthMethod Contract

All login methods implement `Simtabi\Laranail\Auth\Contracts\AuthMethod`:

```php
interface AuthMethod
{
    public function getName(): string;
    public function authenticate(Request $request): ?Authenticatable;
    public function canHandle(Request $request): bool;
    public function validate(Request $request): bool;
    public function getConfig(): array;
}
```

### AuthManager

The `AuthManager` registers and resolves auth methods by name:

```php
use Simtabi\Laranail\Auth\AuthManager;

$manager = app(AuthManager::class);

// Methods are auto-registered by the service provider:
// - 'email'    => EmailPasswordLoginMethod
// - 'username' => UsernamePasswordLoginMethod

// Register a custom method
$manager->registerMethod('otp', OtpLoginMethod::class);

// Resolve a method
$method = $manager->method('email');

// Auto-detect method from request
$method = $manager->resolveMethodForRequest($request);
```

### LaranailGuard

A custom guard driver (`laranail`) that dispatches to registered `AuthMethod` implementations:

```php
// In config/auth.php
'guards' => [
    'web' => [
        'driver' => 'laranail',
        'provider' => 'users',
    ],
],

// Use with attemptWith()
auth()->guard('web')->attemptWith('email', $credentials);
```

## Runtime Components

### Methods

- `EmailPasswordLoginMethod` — implements `AuthMethod`, provides `handle()` with rate limiting
- `UsernamePasswordLoginMethod` — implements `AuthMethod`, provides `handle()` with rate limiting

Both methods support:

- guard-aware authentication
- rate limiting (configurable `maxAttempts` / `decaySeconds`)
- success, failure, and throttled events
- `canHandle()` for automatic method detection via `AuthManager`

### AuthManager

- `registerMethod(name, class)` — register a method by name
- `method(name)` — resolve a method instance
- `methods()` — list registered method names
- `hasMethod(name)` — check if a method is registered
- `resolveMethodForRequest(request)` — auto-detect the method for a request

### LaranailGuard

- Extends `SessionGuard`, implements `StatefulGuard`
- `attemptWith(method, credentials, remember)` — authenticate via a named method
- Fires `Authenticated` and `Failed` events

### Events

| Action         | Events                                                                                          |
|----------------|-------------------------------------------------------------------------------------------------|
| Email login    | `EmailPasswordLoginSuccess`, `EmailPasswordLoginFailed`, `EmailPasswordLoginThrottled`          |
| Username login | `UsernamePasswordLoginSuccess`, `UsernamePasswordLoginFailed`, `UsernamePasswordLoginThrottled` |

## Included Test Support

The package includes a sample `User` model and `UserFactory` for testing and workbench usage.

## Usage

### Email/Password Login

```php
<?php

use Simtabi\Laranail\Auth\Methods\EmailPasswordLoginMethod;

// Default guard
$method = new EmailPasswordLoginMethod();
$method->handle(
    credentials: ['email' => 'user@example.com', 'password' => 'secret'],
    remember: true,
);

// Custom guard
$method = new EmailPasswordLoginMethod(guard: 'admin');
$method->handle(
    credentials: ['email' => 'admin@example.com', 'password' => 'secret'],
);
```

### Username/Password Login

```php
<?php

use Simtabi\Laranail\Auth\Methods\UsernamePasswordLoginMethod;

// Default guard
$method = new UsernamePasswordLoginMethod();
$method->handle(
    credentials: ['username' => 'johndoe', 'password' => 'secret'],
    remember: true,
);

// Custom guard
$method = new UsernamePasswordLoginMethod(guard: 'admin');
$method->handle(
    credentials: ['username' => 'admin_user', 'password' => 'secret'],
);
```

### Custom Guard Setup

First, register the guard and provider in `config/auth.php`:

```php
return [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'admin' => [
            'driver' => 'laranail',
            'provider' => 'admins',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],
];
```

Then use it in your controller:

```php
<?php

namespace App\Http\Controllers\Auth;

use Simtabi\Laranail\Auth\Http\Controllers\EmailPasswordLoginController;

class AdminLoginController extends EmailPasswordLoginController
{
    protected function guard(): ?string
    {
        return 'admin';
    }
}
```

Or use the Method directly:

```php
<?php

use Simtabi\Laranail\Auth\Methods\EmailPasswordLoginMethod;

$method = new EmailPasswordLoginMethod(guard: 'admin');
$method->handle(credentials: ['email' => '...', 'password' => '...']);
```

### AuthManager Usage

```php
<?php

use Simtabi\Laranail\Auth\AuthManager;

$manager = app(AuthManager::class);

// List registered methods
$manager->methods(); // ['email', 'username']

// Resolve a method
$method = $manager->method('email');

// Auto-detect method from request fields
$method = $manager->resolveMethodForRequest($request);

// Register a custom method
$manager->registerMethod('otp', OtpLoginMethod::class);
```

### Custom Guard Driver

Use the `laranail` driver to dispatch to `AuthMethod` implementations via the guard:

```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'laranail',
        'provider' => 'users',
    ],
],
```

```php
// In your controller or anywhere
auth()->guard('web')->attemptWith('email', [
    'email' => 'user@example.com',
    'password' => 'secret',
], remember: true);
```

### Implementing a Custom AuthMethod

```php
<?php

namespace App\Auth\Methods;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Contracts\AuthMethod;

class OtpLoginMethod implements AuthMethod
{
    public function getName(): string
    {
        return 'otp';
    }

    public function canHandle(Request $request): bool
    {
        return $request->has(['email', 'otp_code']);
    }

    public function validate(Request $request): bool
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp_code' => ['required', 'string', 'digits:6'],
        ]);

        return true;
    }

    public function authenticate(Request $request): ?Authenticatable
    {
        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! $user->verifyOtp($request->input('otp_code'))) {
            return null;
        }

        return $user;
    }

    public function getConfig(): array
    {
        return [];
    }
}
```

Register it in a service provider:

```php
<?php

use App\Auth\Methods\OtpLoginMethod;
use Simtabi\Laranail\Auth\AuthManager;

// In boot()
app(AuthManager::class)->registerMethod('otp', OtpLoginMethod::class);
```

### Listening to Events

```php
<?php

use Illuminate\Support\Facades\Event;
use Simtabi\Laranail\Auth\Events\EmailPasswordLoginSuccess;
use Simtabi\Laranail\Auth\Events\EmailPasswordLoginFailed;
use Simtabi\Laranail\Auth\Events\EmailPasswordLoginThrottled;

Event::listen(EmailPasswordLoginSuccess::class, function ($event) {
    Log::info("Login success: {$event->email} via {$event->guard}");
});

Event::listen(EmailPasswordLoginFailed::class, function ($event) {
    Log::warning("Login failed: {$event->email} via {$event->guard}");
});

Event::listen(EmailPasswordLoginThrottled::class, function ($event) {
    Log::critical("Login throttled: {$event->email} via {$event->guard}, retry in {$event->seconds}s");
});
```

## Testing

```bash
composer test
```

## License

MIT
