<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Contracts\AuthMethod;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class AuthManager
{
    protected array $methods = [];

    public function __construct(
        protected AuthFactory $auth,
        protected Request $request,
    ) {
    }

    /**
     * Register an auth method by name.
     *
     * @param  class-string<AuthMethod>  $class
     */
    public function registerMethod(string $name, string $class): static
    {
        $this->methods[$name] = $class;

        return $this;
    }

    /**
     * Get an auth method instance by name.
     */
    public function method(string $name): ?AuthMethod
    {
        if (! isset($this->methods[$name])) {
            return null;
        }

        $class = $this->methods[$name];

        return app($class);
    }

    /**
     * Get all registered method names.
     */
    public function methods(): array
    {
        return array_keys($this->methods);
    }

    /**
     * Check if a method is registered.
     */
    public function hasMethod(string $name): bool
    {
        return isset($this->methods[$name]);
    }

    /**
     * Check if the user is authenticated.
     */
    public function check(): bool
    {
        return $this->auth->guard()->check();
    }

    /**
     * Get the authenticated user.
     */
    public function user(): ?Authenticatable
    {
        return $this->auth->guard()->user();
    }

    /**
     * Get a guard instance.
     */
    public function guard(?string $name = null): Guard
    {
        return $this->auth->guard($name);
    }

    /**
     * Attempt to authenticate the user.
     */
    public function attempt(array $credentials, bool $remember = false): bool
    {
        return $this->auth->guard()->attempt($credentials, $remember);
    }

    /**
     * Log the user out.
     */
    public function logout(): void
    {
        $this->auth->guard()->logout();
    }

    /**
     * Resolve the auth method that can handle the given request.
     */
    public function resolveMethodForRequest(Request $request): ?AuthMethod
    {
        foreach ($this->methods as $name => $class) {
            /** @var AuthMethod $method */
            $method = app($class);

            if ($method->canHandle($request)) {
                return $method;
            }
        }

        return null;
    }

    /**
     * Register a social provider configuration.
     *
     * @param  array{client_id: string, client_secret: string, redirect: string}  $config
     */
    public function registerSocialProvider(string $name, array $config): static
    {
        config(["auth-kit.social.providers.{$name}" => $config]);

        return $this;
    }

    /**
     * Get all registered social providers.
     *
     * @return array<string, array{client_id: string, client_secret: string, redirect: string}>
     */
    public function socialProviders(): array
    {
        return config('auth-kit.social.providers', []);
    }

    /**
     * Check if a social provider is registered.
     */
    public function hasSocialProvider(string $name): bool
    {
        return isset(config('auth-kit.social.providers')[$name]);
    }
}
