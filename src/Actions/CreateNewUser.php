<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use LogicException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Dtos\CreateNewUserInput;
use Illuminate\Contracts\Validation\Factory as Validator;
use Simtabi\Laranail\Auth\Contracts\CreateNewUserInterface;

class CreateNewUser implements CreateNewUserInterface
{
    public function __construct(
        private Validator $validator,
    ) {
    }

    public function execute(CreateNewUserInput $input): Authenticatable
    {
        $model = $this->userModel(guard: $input->guard);

        $data = $this->validator->make(data: [
            'name'                  => $input->name,
            'email'                 => Str::lower(value: $input->email),
            'password'              => $input->password,
            'password_confirmation' => $input->passwordConfirmation,
        ], rules: [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(table: $model, column: 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ])->validate();

        /** @var Model&Authenticatable $user */
        $user = $model::query()->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make(value: $data['password']),
        ]);

        event(new Registered(user: $user));

        return $user;
    }

    private function userModel(string $guard): string
    {
        $model = config(key: 'auth-kit.user_model');

        if (! is_string(value: $model) || $model === '') {
            $provider = config(key: "auth.guards.{$guard}.provider", default: config(key: 'auth.defaults.provider'));
            $model = config(key: "auth.providers.{$provider}.model");
        }

        if (! is_string(value: $model) || ! is_a(object_or_class: $model, class: Model::class, allow_string: true) || ! is_a(object_or_class: $model, class: Authenticatable::class, allow_string: true)) {
            throw new LogicException(message: 'The configured auth-kit user model must be an Eloquent Authenticatable model.');
        }

        return $model;
    }
}
