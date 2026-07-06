<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Simtabi\Laranail\Auth\Methods\EmailPasswordLoginMethod;

abstract class EmailPasswordLoginController
{
    abstract protected function guard(): ?string;

    /**
     * Handle an incoming email/password login request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $method = new EmailPasswordLoginMethod(guard: $this->guard());

        $authenticated = $method->handle(
            credentials: [
                'email'    => $request->string(key: 'email')->toString(),
                'password' => $request->string(key: 'password')->toString(),
            ],
            remember: $request->boolean(key: 'remember'),
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
