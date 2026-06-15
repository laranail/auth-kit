<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Authenticate a user by their email and password.
 *
 * Returns the authenticated user or throws a ValidationException.
 */
class AuthenticateCredentials
{
    /**
     * Attempt to authenticate the user with the given credentials.
     *
     * @param  class-string<Model>  $modelClass  The authenticatable model class.
     * @param  string  $email  The email address.
     * @param  string  $password  The plain-text password.
     * @return Model
     *
     * @throws ValidationException
     */
    public function handle(string $modelClass, string $email, string $password): Model
    {
        $user = $modelClass::resolveByEmail($email);

        if (! $user || ! Hash::check($password, $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        return $user;
    }
}
