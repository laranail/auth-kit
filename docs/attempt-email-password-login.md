# AttemptEmailPasswordLogin

Verify an email address and password against a guard.

## Action

**Class:** `Simtabi\Laranail\Auth\Actions\AttemptEmailPasswordLogin`

```php
public function execute(AttemptEmailPasswordLoginInput $input): AuthResult
```

## Input DTO

**Class:** `Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput`

| Property   | Type     | Required | Default | Description                              |
|------------|----------|----------|---------|------------------------------------------|
| `email`    | `string` | yes      | —       | The email address to authenticate.       |
| `password` | `string` | yes      | —       | The password to verify.                  |
| `guard`    | `string` | yes      | —       | Auth guard to use (e.g. `web`, `admin`). |
| `remember` | `bool`   | no       | `false` | Whether to issue a "remember me" token.  |

## Output

Returns `AuthResult` with one of three statuses:

| Status                  | Meaning                                                                 |
|-------------------------|-------------------------------------------------------------------------|
| `AuthStatus::Passed`    | Credentials are valid. `$result->user` contains the authenticated user. |
| `AuthStatus::Failed`    | Credentials are invalid.                                                |
| `AuthStatus::Throttled` | Too many attempts. `$result->retryAfterSeconds` contains the cooldown.  |

See [AuthResult docs](auth-result.md) for details.

## Contract

**Interface:** `Simtabi\Laranail\Auth\Contracts\AttemptEmailPasswordLoginInterface`

Bound in the service provider so you can resolve via the interface:

```php
$result = app(AttemptEmailPasswordLoginInterface::class)->execute(
    new AttemptEmailPasswordLoginInput(
        email:    $email,
        password: $password,
        guard:    'web',
    )
);
```

## Abstract Controller

**Class:** `Simtabi\Laranail\Auth\Http\Controllers\AbstractAttemptEmailPasswordLoginController`

Invokable controller that handles the full HTTP flow. Extends it to define redirect behavior:

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

| Method        | Signature                                       | Description                                         |
|---------------|-------------------------------------------------|-----------------------------------------------------|
| `passed()`    | `(Request $request, AuthResult $result): mixed` | Called when credentials are valid.                  |
| `failed()`    | `(Request $request, AuthResult $result): mixed` | Called when credentials are invalid.                |
| `throttled()` | `(Request $request, AuthResult $result): mixed` | Called when rate limited. Defaults to `abort(422)`. |

For JSON requests (`Accept: application/json`), the controller returns structured JSON automatically.

See [Abstract controllers docs](abstract-controllers.md) for details.

## Usage

### Basic verification (headless / API)

```php
use Simtabi\Laranail\Auth\Actions\AttemptEmailPasswordLogin;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;

$result = app(AttemptEmailPasswordLogin::class)->execute(
    new AttemptEmailPasswordLoginInput(
        email:    'ada@example.com',
        password: 'secret',
        guard:    'web',
    )
);

if ($result->isPassed()) {
    // credentials are valid
}
```

### Session-based login (web)

```php
use Simtabi\Laranail\Auth\Actions\AttemptEmailPasswordLogin;
use Simtabi\Laranail\Auth\Actions\LoginUser;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;

$attempt = app(AttemptEmailPasswordLogin::class);
$login   = app(LoginUser::class);

$result = $attempt->execute(new AttemptEmailPasswordLoginInput(
    email:    $request->string('email')->toString(),
    password: $request->string('password')->toString(),
    guard:    'web',
    remember: $request->boolean('remember'),
));

if (! $result->isPassed()) {
    return back()->withErrors(['email' => 'Invalid credentials']);
}

$login->execute($result->user, guard: 'web', remember: $request->boolean('remember'));

return redirect()->intended('/dashboard');
```

### Per-module usage (admin vs user)

```php
$result = app(AttemptEmailPasswordLogin::class)->execute(
    new AttemptEmailPasswordLoginInput(
        email:    $email,
        password: $password,
        guard:    'admin',
    )
);

if ($result->isPassed()) {
    app(LoginUser::class)->execute($result->user, guard: 'admin');
}
```
