# LoginUser

Log an `Authenticatable` user into the guard and regenerate the session.

## Action

**Class:** `Simtabi\Laranail\Auth\Actions\LoginUser`

```php
public function execute(Authenticatable $user, bool $remember = false, ?string $guard = null): void
```

This action takes no DTO — it receives the user directly (typically from `AttemptEmailPasswordLogin` or `AttemptUsernameLogin`).

## Parameters

| Parameter  | Type              | Required | Default                    | Description                             |
|------------|-------------------|----------|----------------------------|-----------------------------------------|
| `user`     | `Authenticatable` | yes      | —                          | The user model to log in.               |
| `remember` | `bool`            | no       | `false`                    | Whether to issue a "remember me" token. |
| `guard`    | `string`          | no       | `config('auth-kit.guard')` | Auth guard to log into.                 |

## Output

Returns `void`. The user is authenticated into the session.

## Contract

**Interface:** `Simtabi\Laranail\Auth\Contracts\LoginUserInterface`

Bound in the service provider so you can resolve via the interface:

```php
app(LoginUserInterface::class)->execute($user, remember: true, guard: 'web');
```

## Usage

### Session-based login (chained with credential verification)

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

### Per-module usage

```php
// Admin login
$result = $attempt->execute(new AttemptEmailPasswordLoginInput(
    email:    $email,
    password: $password,
    guard:    'admin',
));

if ($result->isPassed()) {
    $login->execute($result->user, guard: 'admin');
}
```
