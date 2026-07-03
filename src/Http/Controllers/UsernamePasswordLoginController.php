<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Simtabi\Laranail\Auth\Actions\UsernamePasswordLoginAction;
use Simtabi\Laranail\Auth\Http\Requests\UsernamePasswordLoginRequest;

abstract class UsernamePasswordLoginController
{
    abstract protected function guard(): ?string;

    /**
     * Handle an incoming username/password login request.
     *
     * @throws ValidationException
     */
    public function store(
        UsernamePasswordLoginAction $action,
        UsernamePasswordLoginRequest $request,
    ): RedirectResponse {
        $action = new UsernamePasswordLoginAction(guard: $this->guard());

        $authenticated = $action->handle(
            credentials: $request->credentials(),
            remember: $request->remember(),
        );

        if (! $authenticated) {
            throw ValidationException::withMessages(messages: [
                'username' => trans(key: 'auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended();
    }
}
