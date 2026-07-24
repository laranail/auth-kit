# AuthResult

Typed result object returned by authentication actions. Wraps a status and optional data.

## Class

**Namespace:** `Simtabi\Laranail\Auth\Support\AuthResult`

```php
class AuthResult
{
    public AuthStatus $status;
    public ?Authenticatable $user;
    public ?int $retryAfterSeconds;
}
```

## Named constructors

| Method                            | Status      | User | Retry after | Description                                                        |
|-----------------------------------|-------------|------|-------------|--------------------------------------------------------------------|
| `AuthResult::passed($user)`       | `Passed`    | yes  | —           | Credentials are valid.                                             |
| `AuthResult::allowed()`           | `Passed`    | no   | —           | Pre-auth check passed (e.g. rate limit not exceeded). No user yet. |
| `AuthResult::failed()`            | `Failed`    | no   | —           | Credentials are invalid.                                           |
| `AuthResult::throttled($seconds)` | `Throttled` | no   | yes         | Rate limited. `$retryAfterSeconds` is the cooldown.                |

## Helpers

| Method         | Returns | Description                   |
|----------------|---------|-------------------------------|
| `->isPassed()` | `bool`  | `true` if status is `Passed`. |

## AuthStatus enum

**Namespace:** `Simtabi\Laranail\Auth\Enums\AuthStatus`

```php
enum AuthStatus: string
{
    case Passed    = 'passed';
    case Failed    = 'failed';
    case Throttled = 'throttled';
}
```

## Usage

### Match on status

```php
use Simtabi\Laranail\Auth\Enums\AuthStatus;

$result = app(AttemptEmailPasswordLogin::class)->execute(...);

return match ($result->status) {
    AuthStatus::Passed    => redirect()->intended('/dashboard'),
    AuthStatus::Failed    => back()->withErrors(['email' => 'Invalid credentials']),
    AuthStatus::Throttled => response()->json([
        'message'             => 'Too many attempts.',
        'retry_after_seconds' => $result->retryAfterSeconds,
    ], status: 429),
};
```

### Quick check with isPassed()

```php
if ($result->isPassed()) {
    app(LoginUser::class)->execute($result->user, guard: 'web');
}
```

### Check for throttled

```php
if ($result->status === AuthStatus::Throttled) {
    abort(429, "Try again in {$result->retryAfterSeconds}s.");
}
```

## Which actions return AuthResult

| Action                        | Returns                                    |
|-------------------------------|--------------------------------------------|
| `AttemptEmailPasswordLogin`   | `AuthResult` (Passed / Failed / Throttled) |
| `AttemptUsernameLogin`        | `AuthResult` (Passed / Failed / Throttled) |
| `EnforceLoginRateLimitAction` | `AuthResult` (Allowed / Throttled)         |
