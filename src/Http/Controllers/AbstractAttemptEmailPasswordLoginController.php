<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Enums\AuthStatus;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;
use Simtabi\Laranail\Auth\Contracts\AttemptEmailPasswordLoginInterface;
use Simtabi\Laranail\Auth\Http\Requests\AttemptEmailPasswordLoginRequest;

abstract class AbstractAttemptEmailPasswordLoginController extends AbstractAuthController
{
    abstract protected function passed(Request $request, AuthResult $result): mixed;

    abstract protected function failed(Request $request, AuthResult $result): mixed;

    public function store(AttemptEmailPasswordLoginRequest $request, AttemptEmailPasswordLoginInterface $action): mixed
    {
        $result = $action->execute(
            input: new AttemptEmailPasswordLoginInput(
                email: $request->validated(key: 'email'),
                password: $request->validated(key: 'password'),
                guard: $this->guard(),
                remember: (bool) $request->validated(key: 'remember', default: false),
            )
        );

        return match ($result->status) {
            AuthStatus::Passed    => $this->passed(request: $request, result: $result),
            AuthStatus::Failed    => $this->failed(request: $request, result: $result),
            AuthStatus::Throttled => $this->throttled(request: $request, result: $result),
        };
    }

    protected function throttled(Request $request, AuthResult $result): mixed
    {
        abort(code: 429, message: 'Too many attempts.');
    }
}
