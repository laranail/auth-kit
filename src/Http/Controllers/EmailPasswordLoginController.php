<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Simtabi\Laranail\Auth\Actions\EmailPasswordLoginAction;
use Simtabi\Laranail\Auth\Http\Requests\EmailPasswordLoginRequest;

abstract class EmailPasswordLoginController
{
    abstract protected function guard(): ?string;

    /**
     * Handle an incoming email/password login request.
     *
     * @throws ValidationException
     */
    public function store(
        EmailPasswordLoginAction $action,
        EmailPasswordLoginRequest $request,
    ): RedirectResponse {
        $action = new EmailPasswordLoginAction(guard: $this->guard());

        $authenticated = $action->handle(
            credentials: $request->credentials(),
            remember: $request->remember(),
        );

        if (! $authenticated) {
            throw ValidationException::withMessages(messages: [
                'email' => trans(key: 'auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended();
    }
}
