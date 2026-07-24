# CheckEmailExists

Check whether an email address is registered against a guard.

## Action

**Class:** `Simtabi\Laranail\Auth\Actions\CheckEmailExists`

```php
public function execute(CheckEmailExistsInput $input): bool
```

## Input DTO

**Class:** `Simtabi\Laranail\Auth\Dtos\CheckEmailExistsInput`

| Property | Type     | Required | Default | Description                              |
|----------|----------|----------|---------|------------------------------------------|
| `email`  | `string` | yes      | —       | The email address to look up.            |
| `guard`  | `string` | yes      | —       | Auth guard to use (e.g. `web`, `admin`). |

## Output

Returns `bool` — `true` if the email is registered, `false` otherwise.

## Contract

**Interface:** `Simtabi\Laranail\Auth\Contracts\CheckEmailExistsInterface`

Bound in the service provider so you can resolve via the interface:

```php
$exists = app(CheckEmailExistsInterface::class)->execute(
    new CheckEmailExistsInput(
        email: 'user@example.com',
        guard: 'web',
    )
);
```

## Abstract Controller

**Class:** `Simtabi\Laranail\Auth\Http\Controllers\AbstractCheckEmailExistsController`

Invokable controller that handles the full HTTP flow. Extends it to define the non-JSON response:

```php
use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractCheckEmailExistsController;

class CheckEmailController extends AbstractCheckEmailExistsController
{
    protected function respond(Request $request, bool $exists): mixed
    {
        return $exists
            ? back()->withErrors(['email' => 'Email already taken.'])
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
use Simtabi\Laranail\Auth\Actions\CheckEmailExists;
use Simtabi\Laranail\Auth\Dtos\CheckEmailExistsInput;

$exists = app(CheckEmailExists::class)->execute(
    new CheckEmailExistsInput(
        email: 'user@example.com',
        guard: 'web',
    )
);

if ($exists) {
    // email is registered — show login prompt, send password reset, etc.
}
```

### Per-module usage

```php
$exists = app(CheckEmailExists::class)->execute(
    new CheckEmailExistsInput(
        email: $email,
        guard: 'admin',
    )
);
```
