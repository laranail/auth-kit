# AttemptUsernameLogin

Verify a username and password against a guard.

## Action

**Class:** `Simtabi\Laranail\Auth\Actions\AttemptUsernameLogin`

```php
public function execute(AttemptUsernameLoginInput $input): AuthResult
```

## Input DTO

**Class:** `Simtabi\Laranail\Auth\Dtos\AttemptUsernameLoginInput`

| Property   | Type     | Required | Default | Description                              |
|------------|----------|----------|---------|------------------------------------------|
| `username` | `string` | yes      | —       | The username to authenticate.            |
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

**Interface:** `Simtabi\Laranail\Auth\Contracts\AttemptUsernameLoginInterface`

Bound in the service provider so you can resolve via the interface:

```php
$result = app(AttemptUsernameLoginInterface::class)->execute(
    new AttemptUsernameLoginInput(
        username: $username,
        password: $password,
        guard:    'web',
    )
);
```

## Abstract Controller

**Class:** `Simtabi\Laranail\Auth\Http\Controllers\AbstractAttemptUsernameLoginController`

Invokable controller that handles the full HTTP flow. Extends it to define redirect behavior:

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
use Simtabi\Laranail\Auth\Actions\AttemptUsernameLogin;
use Simtabi\Laranail\Auth\Dtos\AttemptUsernameLoginInput;

$result = app(AttemptUsernameLogin::class)->execute(
    new AttemptUsernameLoginInput(
        username: 'ada',
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
use Simtabi\Laranail\Auth\Actions\AttemptUsernameLogin;
use Simtabi\Laranail\Auth\Actions\LoginUser;
use Simtabi\Laranail\Auth\Dtos\AttemptUsernameLoginInput;

$attempt = app(AttemptUsernameLogin::class);
$login   = app(LoginUser::class);

$result = $attempt->execute(new AttemptUsernameLoginInput(
    username: $request->string('username')->toString(),
    password: $request->string('password')->toString(),
    guard:    'web',
    remember: $request->boolean('remember'),
));

if (! $result->isPassed()) {
    return back()->withErrors(['username' => 'Invalid credentials']);
}

$login->execute($result->user, guard: 'web', remember: $request->boolean('remember'));

return redirect()->intended('/dashboard');
```
