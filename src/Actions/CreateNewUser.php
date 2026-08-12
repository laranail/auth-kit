<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Support\UserModelResolver;
use Laravel\Fortify\Contracts\CreatesNewUsers as FortifyCreateNewUser;

class CreateNewUser implements FortifyCreateNewUser
{
    public function create(array $input): Authenticatable
    {
        $model = UserModelResolver::resolve();

        Validator::make(
            data: $input,
            rules: [
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'email', 'max:255', Rule::unique(table: $model)],
                'password' => ['required', 'string', Password::default(), 'confirmed'],
            ]
        )->validate();

        /** @var \Illuminate\Database\Eloquent\Model&Authenticatable $user */
        $user = $model::query()->create([
            'name'     => $input['name'],
            'email'    => Str::lower(value: $input['email']),
            'password' => Hash::make(value: $input['password']),
        ]);

        return $user;
    }
}
