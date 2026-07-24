# LogoutUser

Log the current user out of the guard and invalidate the session.

## Action

**Class:** `Simtabi\Laranail\Auth\Actions\LogoutUser`

```php
public function execute(?string $guard = null): void
```

This action takes no DTO — the guard is the only parameter.

## Parameters

| Parameter | Type     | Required | Default                    | Description                 |
|-----------|----------|----------|----------------------------|-----------------------------|
| `guard`   | `string` | no       | `config('auth-kit.guard')` | Auth guard to log out from. |

## Output

Returns `void`. The user is logged out, the session is invalidated, and the CSRF token is regenerated.

## Contract

**Interface:** `Simtabi\Laranail\Auth\Contracts\LogoutUserInterface`

Bound in the service provider so you can resolve via the interface:

```php
app(LogoutUserInterface::class)->execute(guard: 'web');
```

## Abstract Controller

**Class:** `Simtabi\Laranail\Auth\Http\Controllers\AbstractLogoutController`

Invokable controller that handles the full HTTP flow. Extends it to define the post-logout response:

```php
use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractLogoutController;

class LogoutController extends AbstractLogoutController
{
    protected function loggedOut(Request $request): mixed
    {
        return redirect('/');
    }
}
```

| Method        | Signature                   | Description                                                             |
|---------------|-----------------------------|-------------------------------------------------------------------------|
| `loggedOut()` | `(Request $request): mixed` | Called after the user is logged out. Return whatever response you need. |

See [Abstract controllers docs](abstract-controllers.md) for details.

## Usage

### Basic logout

```php
use Simtabi\Laranail\Auth\Actions\LogoutUser;

app(LogoutUser::class)->execute();

return redirect('/');
```

### Per-module logout

```php
app(LogoutUser::class)->execute(guard: 'admin');

return redirect('/admin/login');
```

### In a controller (using abstract controller)

```php
use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractLogoutController;

class LogoutController extends AbstractLogoutController
{
    protected function loggedOut(Request $request): mixed
    {
        return redirect()->route('login');
    }
}
```
