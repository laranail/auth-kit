# Social Login

Social login via OAuth with bundled Socialite support. Supports Google, Facebook, Twitter, LinkedIn, and PayPal out of the box.

## Supported providers

| Provider | Enum | Config key |
|----------|------|------------|
| Google | `SocialProvider::GOOGLE` | `google` |
| Facebook | `SocialProvider::FACEBOOK` | `facebook` |
| Twitter | `SocialProvider::TWITTER` | `twitter` |
| LinkedIn | `SocialProvider::LINKEDIN` | `linkedin` |
| PayPal | `SocialProvider::PAYPAL` | `paypal` |

## Configuration

Define credentials in `.env`:

```
AUTH_KIT_GOOGLE_CLIENT_ID=
AUTH_KIT_GOOGLE_CLIENT_SECRET=
AUTH_KIT_GOOGLE_REDIRECT=${APP_URL}/auth/google/callback

AUTH_KIT_FACEBOOK_CLIENT_ID=
AUTH_KIT_FACEBOOK_CLIENT_SECRET=
AUTH_KIT_FACEBOOK_REDIRECT=${APP_URL}/auth/facebook/callback

AUTH_KIT_TWITTER_CLIENT_ID=
AUTH_KIT_TWITTER_CLIENT_SECRET=
AUTH_KIT_TWITTER_REDIRECT=${APP_URL}/auth/twitter/callback

AUTH_KIT_LINKEDIN_CLIENT_ID=
AUTH_KIT_LINKEDIN_CLIENT_SECRET=
AUTH_KIT_LINKEDIN_REDIRECT=${APP_URL}/auth/linkedin/callback

AUTH_KIT_PAYPAL_CLIENT_ID=
AUTH_KIT_PAYPAL_CLIENT_SECRET=
AUTH_KIT_PAYPAL_REDIRECT=${APP_URL}/auth/paypal/callback
AUTH_KIT_PAYPAL_SANDBOX_MODE=true
```

Config at `config/auth-kit.php`:

```php
'social' => [
    'google' => [
        'client_id'     => env('AUTH_KIT_GOOGLE_CLIENT_ID'),
        'client_secret' => env('AUTH_KIT_GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('AUTH_KIT_GOOGLE_REDIRECT'),
        'scopes'        => ['openid', 'profile', 'email'],
    ],
    // ... other providers
],
```

Scopes are defined per provider in config and applied automatically by Socialite.

## Actions

### SocialRedirectAction

Generates the OAuth redirect URL.

**Namespace:** `Simtabi\Laranail\Auth\Actions\SocialRedirectAction`

```php
$action = app(SocialRedirectAction::class);

$result = $action->execute(new SocialRedirectActionInput(
    provider: SocialProvider::GOOGLE,
));

// $result->url — the OAuth authorization URL to redirect to
```

**Input:** `SocialRedirectActionInput`

| Property | Type | Description |
|----------|------|-------------|
| `provider` | `SocialProvider` | The OAuth provider enum |
| `state` | `?string` | Optional state parameter |

**Output:** `SocialRedirectResult`

| Property | Type | Description |
|----------|------|-------------|
| `url` | `string` | The OAuth authorization URL |
| `state` | `?string` | The state parameter if provided |

### SocialCallbackAction

Handles the OAuth callback. Accepts a **Closure** for find-or-create user logic.

**Namespace:** `Simtabi\Laranail\Auth\Actions\SocialCallbackAction`

```php
$action = app(SocialCallbackAction::class);

$result = $action->execute(new SocialCallbackActionInput(
    provider: SocialProvider::GOOGLE,
    resolve: function (SocialiteUser $socialUser) {
        // Find or create your user here
        return User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            ['name' => $socialUser->getName()]
        );
    },
));

// $result is an AuthResult
```

**Input:** `SocialCallbackActionInput`

| Property | Type | Description |
|----------|------|-------------|
| `provider` | `SocialProvider` | The OAuth provider enum |
| `resolve` | `Closure(SocialiteUser): ?Authenticatable` | Find-or-create user logic |

**Output:** `AuthResult`

- `AuthResult::passed($user)` — user found/created successfully
- `AuthResult::failed()` — closure returned null or invalid provider

### CreateSocialAccountAction

Creates a Social record via morph. Call from your closure or directly.

**Namespace:** `Simtabi\Laranail\Auth\Actions\CreateSocialAccountAction`

```php
$action = app(CreateSocialAccountAction::class);

$social = $action->execute(new CreateSocialAccountActionInput(
    authenticatable: $user,
    provider: SocialProvider::GOOGLE,
    socialUser: $socialiteUser,
));
```

**Input:** `CreateSocialAccountActionInput`

| Property | Type | Description |
|----------|------|-------------|
| `authenticatable` | `Authenticatable` | The user model to link to |
| `provider` | `SocialProvider` | The OAuth provider enum |
| `socialUser` | `SocialiteUser` | The Socialite user object |

**Output:** `Social` — the created social account model

## Social model

**Namespace:** `Simtabi\Laranail\Auth\Models\Social`

Uses a **morph** relationship (`socialable_type` + `socialable_id`) so it can be linked to any model (User, Admin, etc.).

```php
// Add to your User model
public function socials(): MorphMany
{
    return $this->morphMany(Social::class, 'socialable');
}
```

**Columns:** `id`, `socialable_type`, `socialable_id`, `provider`, `provider_id`, `name`, `nickname`, `email`, `avatar_path`, `token` (encrypted), `refresh_token` (encrypted), `expires_at`, `timestamps`

**Unique constraint:** `['provider', 'provider_id']`

## Migration

The migration is auto-loaded by the service provider. For testing, it's loaded via `defineDatabaseMigrations()`.

To publish:

```bash
php artisan vendor:publish --tag=auth-kit-migrations
```

## Abstract controllers

### AbstractSocialRedirectController

Invokable. Reads `provider` from the route, calls `SocialRedirectAction`, delegates the redirect to the consumer.

```php
abstract class AbstractSocialRedirectController extends AbstractAuthController
{
    public function __invoke(Request $request, SocialRedirectAction $action): mixed;

    abstract protected function redirect(Request $request, string $url): mixed;
}
```

**Example:**

```php
class SocialLoginController extends AbstractSocialRedirectController
{
    protected function redirect(Request $request, string $url): mixed
    {
        return redirect($url);
    }
}
```

### AbstractSocialCallbackController

Invokable. Reads `provider` from the route, calls `SocialCallbackAction` with the consumer's resolve closure, handles JSON responses automatically.

```php
abstract class AbstractSocialCallbackController extends AbstractAuthController
{
    public function __invoke(Request $request, SocialCallbackAction $action): mixed;

    abstract protected function resolve(SocialProvider $provider): Closure;
    abstract protected function passed(Request $request, AuthResult $result): mixed;
    abstract protected function failed(Request $request, AuthResult $result): mixed;
}
```

**Example:**

```php
class SocialCallbackController extends AbstractSocialCallbackController
{
    protected function resolve(SocialProvider $provider): Closure
    {
        return function (SocialiteUser $socialUser) use ($provider) {
            $social = Social::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($social) {
                return $social->socialable;
            }

            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                ['name' => $socialUser->getName()]
            );

            app(CreateSocialAccountAction::class)->execute(
                new CreateSocialAccountActionInput(
                    authenticatable: $user,
                    provider: $provider,
                    socialUser: $socialUser,
                )
            );

            return $user;
        };
    }

    protected function passed(Request $request, AuthResult $result): mixed
    {
        return redirect()->intended('/dashboard');
    }

    protected function failed(Request $request, AuthResult $result): mixed
    {
        return redirect()->route('login')
            ->withErrors(['social' => 'Social authentication failed.']);
    }
}
```

## Routes (consumer-defined)

```php
Route::get('auth/{provider}', SocialLoginController::class)
    ->whereIn('provider', array_column(SocialProvider::cases(), 'value'));

Route::get('auth/{provider}/callback', SocialCallbackController::class)
    ->whereIn('provider', array_column(SocialProvider::cases(), 'value'));
```

## PayPal

PayPal uses a custom provider (`SocialiteProviders\Manager\OAuth2\AbstractProvider`) registered automatically by the service provider. Set `sandbox_mode` in config to switch between sandbox and production.
