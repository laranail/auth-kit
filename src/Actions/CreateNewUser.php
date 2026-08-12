<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use LogicException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\CreatesNewUsers as FortifyCreateNewUser;

class CreateNewUser implements FortifyCreateNewUser
{
    public function create(array $input): Authenticatable
    {
        $model = $this->userModel();

        Validator::make(
            data: $input,
            rules: [
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'string', 'email', 'max:255', Rule::unique(table: $model)],
                'password' => ['required', 'string', Password::default(), 'confirmed'],
            ]
        )->validate();

        /** @var Model&Authenticatable $user */
        $user = $model::query()->create([
            'name'     => $input['name'],
            'email'    => Str::lower(value: $input['email']),
            'password' => Hash::make(value: $input['password']),
        ]);

        return $user;
    }

    private function userModel(): string
    {
        $model = config(key: 'auth-kit.user_model');

        if (!is_string(value: $model) || $model === '') {
            $guard = config(key: 'auth-kit.guard', default: 'web');
            $provider = config(key: "auth.guards.{$guard}.provider", default: config(key: 'auth.defaults.provider'));
            $model = config(key: "auth.providers.{$provider}.model");
        }

        if (!is_string(value: $model) || !is_a(object_or_class: $model, class: Model::class, allow_string: true) || !is_a(object_or_class: $model, class: Authenticatable::class, allow_string: true)) {
            throw new LogicException(message: 'The configured auth-kit user model must be an Eloquent Authenticatable model.');
        }

        return $model;
    }
}
