<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Actions\Register;
use Simtabi\Laranail\Auth\Dtos\RegisterInput;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Http\Requests\RegisterRequest;

abstract class AbstractRegisterController extends AbstractAuthController
{
    public function __invoke(RegisterRequest $request, Register $action): mixed
    {
        $user = $action->execute(
            input: new RegisterInput(
                email:     $request->validated(key: 'email'),
                password:  $request->validated(key: 'password'),
                firstName: $request->validated(key: 'first_name'),
                lastName:  $request->validated(key: 'last_name'),
                username:  $request->validated(key: 'username'),
                guard:     $this->guard(),
            )
        );

        if ($request->expectsJson()) {
            return response()->json(data: [
                'status' => 'registered',
                'user'   => $user,
            ]);
        }

        return $this->registered(request: $request, user: $user);
    }

    abstract protected function registered(Request $request, Authenticatable $user): mixed;
}
