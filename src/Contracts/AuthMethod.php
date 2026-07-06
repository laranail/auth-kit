<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\HttpFoundation\RedirectResponse;

interface AuthMethod
{
    /**
     * Get the method name.
     */
    public function getName(): string;

    /**
     * Authenticate the user.
     */
    public function authenticate(Request $request): ?Authenticatable;

    /**
     * Check if this method can handle the request.
     */
    public function canHandle(Request $request): bool;

    /**
     * Validate the credentials.
     */
    public function validate(Request $request): bool;

    /**
     * Get the configuration for this method.
     */
    public function getConfig(): array;

    /**
     * Redirect to OAuth provider (social methods only).
     * Returns null for non-social methods.
     */
    public function redirect(): ?RedirectResponse;
}
