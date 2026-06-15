<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Models\Concerns;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

/** @mixin Model */
trait HasEmailLogin
{
    /**
     * Resolve a user model instance by email.
     *
     * @param  string  $email  The email address.
     * @return static|null
     */
    public static function resolveByEmail(string $email): ?static
    {
        return static::where('email', $email)->first();
    }

    /**
     * Validate the user's password.
     *
     * @param  string  $password  The plain-text password.
     * @return bool
     */
    public function validatePassword(string $password): bool
    {
        return Hash::check($password, $this->getAuthPassword());
    }

    /**
     * Get the column used as the auth identifier for login.
     */
    public function getAuthIdentifierColumn(): string
    {
        return 'email';
    }

    /**
     * Get the value of the auth identifier for this model instance.
     */
    public function getAuthIdentifierValue(): string
    {
        return (string) $this->email;
    }
}
