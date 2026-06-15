<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Database\Eloquent\Model;

class ResolveUser
{
    /**
     * Find a user by email.
     *
     * @param  class-string<Model>  $modelClass  The authenticatable model class.
     * @param  string  $email  The email address.
     * @return Model|null
     */
    public function handle(string $modelClass, string $email): ?Model
    {
        return $modelClass::resolveByEmail($email);
    }
}
