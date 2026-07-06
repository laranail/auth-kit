<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Methods;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Contracts\AuthMethod;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class BaseAuthMethod implements AuthMethod
{
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    abstract public function authenticate(Request $request): ?Authenticatable;

    abstract public function canHandle(Request $request): bool;

    abstract public function validate(Request $request): bool;

    abstract public function getName(): string;

    public function getConfig(): array
    {
        return $this->config;
    }

    public function redirect(): ?RedirectResponse
    {
        return null;
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
