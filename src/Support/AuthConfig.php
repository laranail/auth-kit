<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Support;

use InvalidArgumentException;

class AuthConfig
{
    public function __construct(
        public string $guard,
        public string $provider,
        public string $model,
    ) {
    }

    public static function fromGuard(?string $guard = null): self
    {
        $guard = $guard
            ?? config(key: 'auth.defaults.guard')
            ?? 'web';

        if (! is_string(value: $guard) || $guard === '') {
            throw new InvalidArgumentException(message: 'Unable to resolve auth guard.');
        }

        $provider = config(key: "auth.guards.{$guard}.provider");

        if (! is_string(value: $provider) || $provider === '') {
            throw new InvalidArgumentException(message: "Auth guard [{$guard}] does not define a valid provider.");
        }

        $driver = config(key: "auth.providers.{$provider}.driver");

        if ($driver !== 'eloquent') {
            throw new InvalidArgumentException(message: "Auth provider [{$provider}] must use the eloquent driver.");
        }

        $model = config(key: "auth.providers.{$provider}.model");

        if (! is_string(value: $model) || $model === '') {
            throw new InvalidArgumentException(message: "Auth provider [{$provider}] does not define a valid model.");
        }

        return new self(
            guard: $guard,
            provider: $provider,
            model: $model,
        );
    }
}
