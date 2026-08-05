<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Dtos\CreateNewUserInput;
use Simtabi\Laranail\Auth\Contracts\CreateNewUserInterface;
use Simtabi\Laranail\Auth\Http\Requests\CreateNewUserRequest;

abstract class AbstractRegisterController extends AbstractAuthController
{
    abstract protected function registered(Request $request, Authenticatable $user): mixed;

    public function store(CreateNewUserRequest $request, CreateNewUserInterface $creator): mixed
    {
        $user = $creator->execute(input: new CreateNewUserInput(
            name: $request->validated(key: 'name'),
            email: $request->validated(key: 'email'),
            password: $request->validated(key: 'password'),
            passwordConfirmation: $request->input(key: 'password_confirmation', default: ''),
            guard: $this->guard(),
        ));

        return $this->registered(request: $request, user: $user);
    }
}
