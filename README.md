# Laravel Auth Kit

A headless, action-based authentication toolkit for Laravel.

No views. No routes. No controllers. No frontend assumptions.

The package ships single-purpose **Action** classes that take a typed input DTO and return a typed result. You compose them wherever you need auth — HTTP controllers, Livewire components, Artisan commands, queue jobs, or module-specific service providers in a modular monolith.

## Why

Most Laravel auth packages ship UI, routes, and opinions. This one ships **plain PHP objects**:

- Actions have one public method: `execute()`.
- Inputs are `readonly` DTOs. No arrays across boundaries.
- Outputs are `readonly` result objects. No `bool`, no `?User`.
- No exceptions for expected outcomes (wrong password, throttled). Exceptions are reserved for programmer errors.
- Every action is injectable, mockable, and testable in isolation.

This makes the package reusable across multiple modules of the same app — e.g. `user` and `admin` modules — each with its own guard.

## Requirements

| Version | PHP          | Laravel |
|---------|--------------|---------|
| 1.x     | 8.4.x, 8.5.x | 13.x    |

## Installation

```bash
composer require laranail/auth-kit
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag=auth-kit-config
```

## Configuration

`config/auth-kit.php`:

```php
return [
    // The default guard used when an action is invoked without an explicit guard.
    'guard' => env('AUTH_KIT_GUARD', 'web'),
];
```

Guards and user providers are defined the standard Laravel way in `config/auth.php`. This package does not ship a custom guard driver — it delegates to whatever guards you configure.

## Concepts

### Actions

An action is a single-purpose class with one public method:

```php
final readonly class SomeAction
{
    public function __construct(/* injected deps */) {}

    public function execute(SomeInput $input): SomeResult
    {
        // one thing, well
    }
}
```

Resolve actions from the container so their dependencies are wired for you:

```php
$action = app(SomeAction::class);
```

### Result object

`AuthResult` represents the outcome of any authentication attempt. It has three shapes:

| Named constructor                 | Status                | Extra data                    |
|-----------------------------------|-----------------------|-------------------------------|
| `AuthResult::passed($user)`       | `AuthStatus::Passed`  | `user`                        |
| `AuthResult::failed()`            | `AuthStatus::Failed`  | —                             |
| `AuthResult::throttled($seconds)` | `AuthStatus::Throttled` | `retryAfterSeconds`         |

Check the outcome with `->isPassed()` or by switching on `->status`.

### Composition

Actions compose. Verifying credentials and logging the user into the session are **separate actions** so headless callers (API, mobile, queue jobs) can skip session concerns entirely, while session-based callers chain them together.

## Available actions

| Action                                                                   | Purpose                                                            |
|--------------------------------------------------------------------------|--------------------------------------------------------------------|
| `Simtabi\Laranail\Auth\Actions\Password\AttemptPasswordLoginAction`      | Verify email + password against a guard. Returns an `AuthResult`.  |
| `Simtabi\Laranail\Auth\Actions\Session\LoginUserAction`                  | Log an `Authenticatable` into the guard and regenerate the session. |

More actions (username login, rate limiting, logout, social, 2FA) will be added incrementally. See [Roadmap](#roadmap).

## Usage

### Headless password verification (API / stateless)

```php
use Simtabi\Laranail\Auth\Actions\Password\AttemptPasswordLoginAction;
use Simtabi\Laranail\Auth\Actions\Password\AttemptPasswordLoginInput;
use Simtabi\Laranail\Auth\Results\AuthStatus;

$result = app(AttemptPasswordLoginAction::class)->execute(
    new AttemptPasswordLoginInput(
        email: 'ada@example.com',
        password: 'secret',
    ),
);

return match ($result->status) {
    AuthStatus::Passed    => response()->json(['user' => $result->user]),
    AuthStatus::Failed    => response()->json(['message' => 'Invalid credentials'], 422),
    AuthStatus::Throttled => response()->json(
        ['message' => "Try again in {$result->retryAfterSeconds}s"],
        429,
    ),
};
```

### Session-based login (web)

Chain credential verification with a session login:

```php
use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Actions\Password\AttemptPasswordLoginAction;
use Simtabi\Laranail\Auth\Actions\Password\AttemptPasswordLoginInput;
use Simtabi\Laranail\Auth\Actions\Session\LoginUserAction;

public function store(
    Request $request,
    AttemptPasswordLoginAction $attempt,
    LoginUserAction $login,
) {
    $result = $attempt->execute(new AttemptPasswordLoginInput(
        email:    $request->string('email')->toString(),
        password: $request->string('password')->toString(),
        remember: $request->boolean('remember'),
    ));

    if (! $result->isPassed()) {
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    $login->execute($result->user, remember: $request->boolean('remember'));

    return redirect()->intended();
}
```

### Per-module usage (admin vs user)

The same actions serve any number of modules. Pass the target guard on the input DTO:

```php
// In the admin module
$result = app(AttemptPasswordLoginAction::class)->execute(
    new AttemptPasswordLoginInput(
        email:    $email,
        password: $password,
        guard:    'admin',
    ),
);

if ($result->isPassed()) {
    app(LoginUserAction::class)->execute($result->user, guard: 'admin');
}
```

Define the `admin` guard the standard Laravel way in `config/auth.php`:

```php
'guards' => [
    'web'   => ['driver' => 'session', 'provider' => 'users'],
    'admin' => ['driver' => 'session', 'provider' => 'admins'],
],

'providers' => [
    'users'  => ['driver' => 'eloquent', 'model' => App\Models\User::class],
    'admins' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class],
],
```

Each module can wrap the actions in a thin controller of its own — the package deliberately does not ship one.

### Using actions from a queue job

Because actions don't touch `Request` or `Response`, they run anywhere:

```php
final class VerifyImportedCredentialsJob implements ShouldQueue
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public function handle(AttemptPasswordLoginAction $attempt): void
    {
        $result = $attempt->execute(new AttemptPasswordLoginInput(
            email:    $this->email,
            password: $this->password,
        ));

        // Do something with $result->status …
    }
}
```

## Testing your integration

Actions are trivial to mock because they have one method:

```php
use Simtabi\Laranail\Auth\Actions\Password\AttemptPasswordLoginAction;
use Simtabi\Laranail\Auth\Results\AuthResult;

it('shows an error when credentials are invalid', function () {
    $this->mock(AttemptPasswordLoginAction::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn(AuthResult::failed());

    $this->post('/login', ['email' => 'x@y.z', 'password' => 'bad'])
        ->assertSessionHasErrors('email');
});
```

## Running the package's own tests

```bash
composer test
```

## Roadmap

The package is intentionally minimal today. Future action additions:

- Rate limiting (`EnforceLoginRateLimitAction`)
- Username-based login (`AttemptUsernameLoginAction`)
- Logout (`LogoutUserAction`)
- Social login (redirect + callback actions)
- 2FA / MFA (challenge issue + verify actions with TOTP, OTP, recovery codes)
- Optional HTTP companion package (`laranail/auth-kit-http`) with controllers and route macros

## License

MIT
```