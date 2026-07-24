# FindUserByEmail

Retrieve a user by email address from a guard's user provider.

## Action

**Class:** `Simtabi\Laranail\Auth\Actions\FindUserByEmail`

```php
public function execute(FindUserByEmailInput $input): ?Authenticatable
```

## Input DTO

**Class:** `Simtabi\Laranail\Auth\Dtos\FindUserByEmailInput`

| Property | Type     | Required | Default | Description                              |
|----------|----------|----------|---------|------------------------------------------|
| `email`  | `string` | yes      | —       | The email address to look up.            |
| `guard`  | `string` | yes      | —       | Auth guard to use (e.g. `web`, `admin`). |

## Output

Returns `?Authenticatable` — the user model if found, `null` otherwise.

## Contract

**Interface:** `Simtabi\Laranail\Auth\Contracts\FindUserByEmailInterface`

Bound in the service provider so you can resolve via the interface:

```php
$user = app(FindUserByEmailInterface::class)->execute(
    new FindUserByEmailInput(
        email: 'user@example.com',
        guard: 'web',
    )
);
```

## Usage

### Basic lookup

```php
use Simtabi\Laranail\Auth\Actions\FindUserByEmail;
use Simtabi\Laranail\Auth\Dtos\FindUserByEmailInput;

$user = app(FindUserByEmail::class)->execute(
    new FindUserByEmailInput(
        email: 'user@example.com',
        guard: 'web',
    )
);

if ($user) {
    // user found
}
```

### Per-module usage

```php
$user = app(FindUserByEmail::class)->execute(
    new FindUserByEmailInput(
        email: $email,
        guard: 'admin',
    )
);
```
