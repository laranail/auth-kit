# FindUserByUsername

Retrieve a user by username from a guard's user provider.

## Action

**Class:** `Simtabi\Laranail\Auth\Actions\FindUserByUsername`

```php
public function execute(FindUserByUsernameInput $input): ?Authenticatable
```

## Input DTO

**Class:** `Simtabi\Laranail\Auth\Dtos\FindUserByUsernameInput`

| Property   | Type     | Required | Default | Description                              |
|------------|----------|----------|---------|------------------------------------------|
| `username` | `string` | yes      | —       | The username to look up.                 |
| `guard`    | `string` | yes      | —       | Auth guard to use (e.g. `web`, `admin`). |

## Output

Returns `?Authenticatable` — the user model if found, `null` otherwise.

## Contract

**Interface:** `Simtabi\Laranail\Auth\Contracts\FindUserByUsernameInterface`

Bound in the service provider so you can resolve via the interface:

```php
$user = app(FindUserByUsernameInterface::class)->execute(
    new FindUserByUsernameInput(
        username: 'ada',
        guard:    'web',
    )
);
```

## Usage

### Basic lookup

```php
use Simtabi\Laranail\Auth\Actions\FindUserByUsername;
use Simtabi\Laranail\Auth\Dtos\FindUserByUsernameInput;

$user = app(FindUserByUsername::class)->execute(
    new FindUserByUsernameInput(
        username: 'ada',
        guard:    'web',
    )
);

if ($user) {
    // user found
}
```

### Per-module usage

```php
$user = app(FindUserByUsername::class)->execute(
    new FindUserByUsernameInput(
        username: $username,
        guard:    'admin',
    )
);
```
