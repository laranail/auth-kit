<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Enums\AuthStatus;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\Auth\Actions\AttemptUsernameLogin;
use Simtabi\Laranail\Auth\Dtos\AttemptUsernameLoginInput;
use Simtabi\Laranail\Auth\Http\Requests\AttemptUsernameLoginRequest;

abstract class AbstractAttemptUsernameLoginController extends AbstractAuthController
{
    public function __invoke(AttemptUsernameLoginRequest $request, AttemptUsernameLogin $action): mixed
    {
        $result = $action->execute(
            input: new AttemptUsernameLoginInput(
                username: $request->validated('username'),
                password: $request->validated('password'),
                remember: (bool) $request->validated('remember', false),
                guard: $this->guard(),
            )
        );

        if ($request->expectsJson()) {
            return match ($result->status) {
                AuthStatus::Passed => response()->json(data: [
                    'status' => 'passed',
                    'user'   => $result->user,
                ]),
                AuthStatus::Failed => response()->json(data: [
                    'status'  => 'failed',
                    'message' => 'Invalid credentials.',
                ], status: 422),
                AuthStatus::Throttled => response()->json(data: [
                    'status'              => 'throttled',
                    'message'             => 'Too many attempts.',
                    'retry_after_seconds' => $result->retryAfterSeconds,
                ], status: 429),
            };
        }

        return match ($result->status) {
            AuthStatus::Passed    => $this->passed(request: $request, result: $result),
            AuthStatus::Failed    => $this->failed(request: $request, result: $result),
            AuthStatus::Throttled => $this->throttled(request: $request, result: $result),
        };
    }

    abstract protected function passed(Request $request, AuthResult $result): mixed;

    abstract protected function failed(Request $request, AuthResult $result): mixed;

    protected function throttled(Request $request, AuthResult $result): mixed
    {
        abort(code: 429, message: 'Too many attempts.');
    }
}
