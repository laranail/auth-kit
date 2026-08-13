<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Simtabi\Laranail\Auth\Support\AuthKit;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    public function update($user, array $input): void
    {
        $guard = AuthKit::guard();

        Validator::make(data: $input, rules: [
            'current_password' => ['required', 'string', "current_password:{$guard}"],
            'password'         => ['required', 'string', Password::default(), 'confirmed'],
        ], messages: [
            'current_password.current_password' => __(key: 'The provided password does not match your current password.'),
        ])->validateWithBag(errorBag: 'updatePassword');

        $user->forceFill(attributes: [
            'password' => Hash::make(value: $input['password']),
        ])->save();
    }
}
