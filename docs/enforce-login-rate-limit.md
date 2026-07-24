# EnforceLoginRateLimitAction

Check and increment a rate limit counter before allowing a login attempt.

## Action

**Class:** `Simtabi\Laranail\Auth\Actions\EnforceLoginRateLimitAction`

```php
public function execute(EnforceLoginRateLimitInput $input): AuthResult
```

## Input DTO

**Class:** `Simtabi\Laranail\Auth\Dtos\EnforceLoginRateLimitInput`

| Property | Type     | Required | Default | Description                                                 |
|----------|----------|----------|---------|-------------------------------------------------------------|
| `key`    | `string` | yes      | —       | Identifier for the rate limit (e.g. the email or username). |
| `guard`  | `string` | yes      | —       | Auth guard — each guard maintains its own counter.          |

The rate limit key is composed internally as `login:{guard}:{key}`.

## Configuration

In `config/auth-kit.php`:

```php
'rate_limit' => [
    'max_attempts'  => (int) env('AUTH_KIT_RATE_LIMIT_MAX_ATTEMPTS', 5),
    'decay_minutes' => (int) env('AUTH_KIT_RATE_LIMIT_DECAY_MINUTES', 1),
],
```

| Key             | Env var                             | Default | Description                       |
|-----------------|-------------------------------------|---------|-----------------------------------|
| `max_attempts`  | `AUTH_KIT_RATE_LIMIT_MAX_ATTEMPTS`  | `5`     | Max attempts before throttling.   |
| `decay_minutes` | `AUTH_KIT_RATE_LIMIT_DECAY_MINUTES` | `1`     | Minutes until the counter resets. |

## Output

Returns `AuthResult` with one of two statuses:

| Status                  | Meaning                                                                |
|-------------------------|------------------------------------------------------------------------|
| `AuthStatus::Passed`    | Request is allowed (via `AuthResult::allowed()`).                      |
| `AuthStatus::Throttled` | Too many attempts. `$result->retryAfterSeconds` contains the cooldown. |

## Contract

**Interface:** `Simtabi\Laranail\Auth\Contracts\EnforceLoginRateLimitInterface`

Bound in the service provider so you can resolve via the interface:

```php
$result = app(EnforceLoginRateLimitInterface::class)->execute(
    new EnforceLoginRateLimitInput(
        key:   $email,
        guard: 'web',
    )
);
```

## Usage

### Pre-login rate limiting

```php
use Simtabi\Laranail\Auth\Actions\EnforceLoginRateLimitAction;
use Simtabi\Laranail\Auth\Dtos\EnforceLoginRateLimitInput;

$limiter = app(EnforceLoginRateLimitAction::class);

$check = $limiter->execute(new EnforceLoginRateLimitInput(
    key:   $request->string('email')->toString(),
    guard: 'web',
));

if ($check->status === AuthStatus::Throttled) {
    return response()->json([
        'message'             => 'Too many attempts.',
        'retry_after_seconds' => $check->retryAfterSeconds,
    ], status: 429);
}
```

### Per-module usage

```php
$check = $limiter->execute(new EnforceLoginRateLimitInput(
    key:   $email,
    guard: 'admin',
));
```

Each guard maintains its own rate limit counter — logging in via `web` won't affect `admin`.

## Usage with AttemptEmailPasswordLogin

Rate limiting is typically enforced before attempting credentials:

```php
use Simtabi\Laranail\Auth\Actions\EnforceLoginRateLimitAction;
use Simtabi\Laranail\Auth\Actions\AttemptEmailPasswordLogin;
use Simtabi\Laranail\Auth\Dtos\EnforceLoginRateLimitInput;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;
use Simtabi\Laranail\Auth\Enums\AuthStatus;

$limiter = app(EnforceLoginRateLimitAction::class);
$attempt = app(AttemptEmailPasswordLogin::class);

$check = $limiter->execute(new EnforceLoginRateLimitInput(
    key:   $request->string('email')->toString(),
    guard: 'web',
));

if ($check->status === AuthStatus::Throttled) {
    abort(429, "Try again in {$check->retryAfterSeconds}s.");
}

$result = $attempt->execute(new AttemptEmailPasswordLoginInput(
    email:    $request->string('email')->toString(),
    password: $request->string('password')->toString(),
    guard:    'web',
));

// ... handle result
```
