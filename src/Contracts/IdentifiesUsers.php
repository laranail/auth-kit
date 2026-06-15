<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Database\Eloquent\Model;

interface IdentifiesUsers
{
    /**
     * Find a user by their email.
     *
     * @param  class-string<Model>  $model  The authenticatable model class.
     * @param  string  $email  The email address.
     * @return Model|null
     */
    public function resolveByCredentials(string $model, string $email): ?Model;

    /**
     * Validate the given credentials against the user.
     *
     * @param  Model  $user  The authenticatable model instance.
     * @param  string  $password  The plain-text password to verify.
     * @return bool
     */
    public function validateCredentials(Model $user, string $password): bool;
}
