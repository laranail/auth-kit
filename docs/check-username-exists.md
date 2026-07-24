# CheckUsernameExists

Check whether a username is registered against a guard.

## Action

**Class:** `Simtabi\Laranail\Auth\Actions\CheckUsernameExists`

```php
public function execute(CheckUsernameExistsInput $input): bool
```

## Input DTO

**Class:** `Simtabi\Laranail\Auth\Dtos\CheckUsernameExistsInput`

| Property   | Type     | Required | Default | Description                              |
|------------|----------|----------|---------|------------------------------------------|
| `username` | `string` | yes      | —       | The username to look up.                 |
| `guard`    | `string` | yes      | —       | Auth guard to use (e.g. `web`, `admin`). |

## Output

Returns `bool` — `true` if the username is registered, `false` otherwise.

## Contract

**Interface:** `Simtabi\Laranail\Auth\Contracts\CheckUsernameExistsInterface`

Bound in the service provider so you can resolve via the interface:

```php
$exists = app(CheckUsernameExistsInterface::class)->execute(
    new CheckUsernameExistsInput(
        username: 'ada',
        guard:    'web',
    )
);
```

## Abstract Controller

**Class:** `Simtabi\Laranail\Auth\Http\Controllers\AbstractCheckUsernameExistsController`

Invokable controller that handles the full HTTP flow. Extends it to define the non-JSON response:

```php
use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractCheckUsernameExistsController;

class CheckUsernameController extends AbstractCheckUsernameExistsController
{
    protected function respond(Request $request, bool $exists): mixed
    {
        return $exists
            ? back()->withErrors(['username' => 'Username already taken.'])
            : back();
    }
}
```

| Method      | Signature                                 | Description                    |
|-------------|-------------------------------------------|--------------------------------|
| `respond()` | `(Request $request, bool $exists): mixed` | Called with the lookup result. |

For JSON requests (`Accept: application/json`), the controller returns `{"exists": true}` or `{"exists": false}` automatically.

See [Abstract controllers docs](abstract-controllers.md) for details.

## Usage

### Basic lookup

```php
use Simtabi\Laranail\Auth\Actions\CheckUsernameExists;
use Simtabi\Laranail\Auth\Dtos\CheckUsernameExistsInput;

$exists = app(CheckUsernameExists::class)->execute(
    new CheckUsernameExistsInput(
        username: 'ada',
        guard:    'web',
    )
);

if ($exists) {
    // username is registered — show login prompt, suggest alternatives, etc.
}
```

### Per-module usage

```php
$exists = app(CheckUsernameExists::class)->execute(
    new CheckUsernameExistsInput(
        username: $username,
        guard:    'admin',
    )
);
```
