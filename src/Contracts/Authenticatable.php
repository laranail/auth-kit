<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

interface Authenticatable
{
    /**
     * Get the column used as the primary auth identifier (email).
     */
    public function getAuthIdentifierColumn(): string;

    /**
     * Get the value of the auth identifier for this model instance.
     */
    public function getAuthIdentifierValue(): string;

    /**
     * Get the model's password.
     */
    public function getAuthPassword(): string;

    /**
     * Get the name of the column for the "remember me" token.
     */
    public function getRememberTokenName(): string;
}
