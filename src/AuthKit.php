<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth;

use Simtabi\Laranail\Auth\Support\AuthConfig;

class AuthKit
{
    public function __construct(
        private ?string $guard = null,
    ) {
    }

    public static function guard(string $guard): self
    {
        return new self($guard);
    }

    public function getGuard(): string
    {
        return AuthConfig::fromGuard($this->guard)->guard;
    }

    public function getProvider(): string
    {
        return AuthConfig::fromGuard($this->guard)->provider;
    }

    public function getModel(): string
    {
        return AuthConfig::fromGuard($this->guard)->model;
    }
}
