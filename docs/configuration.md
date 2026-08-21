# Configuration

Publish the configuration before changing defaults:

```bash
php artisan vendor:publish --tag=auth-kit-config
```

Set `AUTH_KIT_GUARD` to the application's intended guard. The `auth-kit.fortify.features` list enables Fortify capabilities individually: `reset-passwords`, `update-profile-information`, `update-passwords`, `email-verification`, and `passkeys`. Removing an item prevents Auth Kit from enabling that capability; remove corresponding application routes and UI too.

Credential throttling is controlled by `AUTH_KIT_RATE_LIMIT_MAX_ATTEMPTS` and `AUTH_KIT_RATE_LIMIT_DECAY_MINUTES`, defaulting to five attempts per minute. Social credentials, callbacks, and enabled providers are configured in `auth-kit.social`; see [social login](social-login.md) for provider settings.