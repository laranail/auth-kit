# Abstract Controllers

The package ships abstract controllers that handle JSON responses automatically and delegate non-JSON responses to overridable methods. Extend them to wire up your own routes.

## Overview

All abstract controllers extend `AbstractAuthController`, which provides a `guard()` helper reading from `config('auth-kit.guard')`.

| Controller                                    | Action                      | Overridable methods                   |
|-----------------------------------------------|-----------------------------|---------------------------------------|
| `AbstractAttemptEmailPasswordLoginController` | `AttemptEmailPasswordLogin` | `passed()`, `failed()`, `throttled()` |
| `AbstractAttemptUsernameLoginController`      | `AttemptUsernameLogin`      | `passed()`, `failed()`, `throttled()` |
| `AbstractCheckEmailExistsController`          | `CheckEmailExists`          | `respond()`                           |
| `AbstractCheckUsernameExistsController`       | `CheckUsernameExists`       | `respond()`                           |
| `AbstractLogoutController`                    | `LogoutUser`                | `loggedOut()`                         |

## JSON behavior

For JSON requests (`Accept: application/json` or `$request->expectsJson()`), all abstract controllers return structured JSON automatically. No override needed.

### Login controllers (email + password / username)

```json
// Passed (200)
{ "status": "passed", "user": { ... } }

// Failed (422)
{ "status": "failed", "message": "Invalid credentials." }

// Throttled (429)
{ "status": "throttled", "message": "Too many attempts.", "retry_after_seconds": 45 }
```

### Check-exists controllers

```json
{ "exists": true }
{ "exists": false }
```

### Logout controller

Returns empty 200 on logout.

## AbstractAttemptEmailPasswordLoginController

**Namespace:** `Simtabi\Laranail\Auth\Http\Controllers`

Invokable. Accepts `AttemptEmailPasswordLoginRequest` + `AttemptEmailPasswordLogin` via the container.

```php
abstract class AbstractAttemptEmailPasswordLoginController extends AbstractAuthController
{
    abstract protected function passed(Request $request, AuthResult $result): mixed;
    abstract protected function failed(Request $request, AuthResult $result): mixed;

    protected function throttled(Request $request, AuthResult $result): mixed
    {
        abort(429, 'Too many attempts.');
    }
}
```

| Method        | Called when             | Default            |
|---------------|-------------------------|--------------------|
| `passed()`    | Credentials are valid   | — (must implement) |
| `failed()`    | Credentials are invalid | — (must implement) |
| `throttled()` | Rate limited            | `abort(429)`       |

**Example:**

```php
use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractAttemptEmailPasswordLoginController;
use Simtabi\Laranail\Auth\Support\AuthResult;

class LoginController extends AbstractAttemptEmailPasswordLoginController
{
    protected function passed(Request $request, AuthResult $result): mixed
    {
        return redirect()->intended('/dashboard');
    }

    protected function failed(Request $request, AuthResult $result): mixed
    {
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }
}
```

## AbstractAttemptUsernameLoginController

Identical structure to the email+password controller, but uses `AttemptUsernameLoginRequest` + `AttemptUsernameLogin`.

```php
abstract class AbstractAttemptUsernameLoginController extends AbstractAuthController
{
    abstract protected function passed(Request $request, AuthResult $result): mixed;
    abstract protected function failed(Request $request, AuthResult $result): mixed;

    protected function throttled(Request $request, AuthResult $result): mixed
    {
        abort(429, 'Too many attempts.');
    }
}
```

**Example:**

```php
use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Http\Controllers\AbstractAttemptUsernameLoginController;
use Simtabi\Laranail\Auth\Support\AuthResult;

class UsernameLoginController extends AbstractAttemptUsernameLoginController
{
    protected function passed(Request $request, AuthResult $result): mixed
    {
        return redirect()->intended('/dashboard');
    }

    protected function failed(Request $request, AuthResult $result): mixed
    {
        return back()->withErrors(['username' => 'Invalid credentials.']);
    }
}
```

## AbstractCheckEmailExistsController

Invokable. Accepts `CheckEmailExistsRequest` + `CheckEmailExists` via the container.

```php
abstract class AbstractCheckEmailExistsController extends AbstractAuthController
{
    abstract protected function respond(Request $request, bool $exists): mixed;
}
```

| Method      | Called when     | Default            |
|-------------|-----------------|--------------------|
| `respond()` | Lookup complete | — (must implement) |

**Example:**

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

## AbstractCheckUsernameExistsController

Identical structure to the email controller, but for usernames.

```php
abstract class AbstractCheckUsernameExistsController extends AbstractAuthController
{
    abstract protected function respond(Request $request, bool $exists): mixed;
}
```

**Example:**

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

## AbstractLogoutController

Invokable. Accepts `LogoutUser` via the container.

```php
abstract class AbstractLogoutController extends AbstractAuthController
{
    abstract protected function loggedOut(Request $request): mixed;
}
```

| Method        | Called when        | Default            |
|---------------|--------------------|--------------------|
| `loggedOut()` | User is logged out | — (must implement) |

**Example:**

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
