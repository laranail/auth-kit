# Laravel Auth Kit

`laranail/auth-kit` provides lightweight Laravel authentication actions, form requests, and events.

## Requirements

| Version | PHP | Laravel |
|---------|-----|---------|
| 1.x | 8.4.x, 8.5.x | 13.x |

## Installation

```bash
composer require laranail/auth-kit
```

## Conventions

This package follows Laravel auth conventions:

| Field | Column |
|-------|--------|
| Email login | `email` |
| Username login | `username` |
| Password | `password` |
| Remember token | `remember_token` |

No column customization is supported.

## Guard Resolution

Actions accept an optional guard name.

If no guard is provided, the package resolves the guard from:

1. `auth.defaults.guard`
2. fallback to `web`

The resolved provider and model are read from Laravel's `auth` configuration.

## Runtime Components

### Actions

- `EmailPasswordLoginAction`
- `UsernamePasswordLoginAction`

Both actions support:

- guard-aware authentication
- rate limiting
- session regeneration
- success, failure, and throttled events

### Form Requests

- `EmailPasswordLoginRequest`
- `UsernamePasswordLoginRequest`

### Events

| Action | Events |
|--------|--------|
| Email login | `EmailPasswordLoginSuccess`, `EmailPasswordLoginFailed`, `EmailPasswordLoginThrottled` |
| Username login | `UsernamePasswordLoginSuccess`, `UsernamePasswordLoginFailed`, `UsernamePasswordLoginThrottled` |

## Included Test Support

The package includes a sample `User` model and `UserFactory` for testing and workbench usage.

## Usage

```php
<?php

use Simtabi\Laranail\Auth\Actions\EmailPasswordLoginAction;

$action = new EmailPasswordLoginAction();
$action->handle($request);
```

Using a specific guard:

```php
<?php

use Simtabi\Laranail\Auth\Actions\EmailPasswordLoginAction;

$action = new EmailPasswordLoginAction(guard: 'admin');
$action->handle($request);
```

## Testing

```bash
composer test
```

## License

MIT
