# Laravel Auth Kit

`laranail/auth-kit` is a publish-first Laravel authentication scaffolding package supporting multiple authenticatable models.

The package generates routes, controllers, migrations, and actions for email/password authentication. Guards are managed in Laravel's auth config.

The package is managed by `Simtabi\Laranail\Auth\AuthKitServiceProvider` and exposed through the `auth-kit:install` Artisan command.

## Requirements

| Version | PHP          | Laravel    |
|---------|--------------|------------|
| 1.x     | 8.4.x, 8.5.x | 13.x       |

## Installation

```bash
composer require laranail/auth-kit
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=auth-kit-config --provider="Simtabi\Laranail\Auth\AuthKitServiceProvider"
```

The package configuration lives in `config/auth-kit.php`.

## Usage

Run the interactive install command to scaffold authentication for a model:

```bash
php artisan auth-kit:install
```

The command will prompt you to:

1. Select the authenticatable model
2. Set a route prefix and redirect path

## Auth Providers

After running the install command, add the provider to `config/auth.php`:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Models\User::class,
    ],

    // Add your model's provider:
    'admins' => [
        'driver' => 'eloquent',
        'model'  => App\Models\Admin::class,
    ],
],
```

Then set the guard's provider in `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver'   => 'session',
        'provider' => 'admins', // Use your model's provider key
    ],
],
```

## Migrations

The package creates migrations for the authenticatable model's table. You can also publish stubs and customize them:

```bash
php artisan vendor:publish --tag=auth-kit-stubs --provider="Simtabi\Laranail\Auth\AuthKitServiceProvider"
```

## Traits

The package provides the `HasEmailLogin` trait for authenticatable models:

```php
use Simtabi\Laranail\Auth\Models\Concerns\HasEmailLogin;

class User extends Authenticatable
{
    use HasEmailLogin;
}
```

This trait provides:
- `resolveByEmail(string $email)` - Find a user by email
- `validatePassword(string $password)` - Validate the user's password

## Actions

The package provides the following actions:

- `AuthenticateCredentials` - Authenticate a user by email and password
- `IssueSession` - Issue an authenticated session
- `ResolveUser` - Resolve a user by email

## Testing

```bash
composer test
```

## License

MIT
