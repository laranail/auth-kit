<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Simtabi\Laranail\Auth\Methods\UsernamePasswordLoginMethod;

abstract class UsernamePasswordLoginController
{
    abstract protected function guard(): ?string;

    /**
     * Handle an incoming username/password login request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $method = new UsernamePasswordLoginMethod(guard: $this->guard());

        $authenticated = $method->handle(
            credentials: [
                'username' => $request->string(key: 'username')->toString(),
                'password' => $request->string(key: 'password')->toString(),
            ],
            remember: $request->boolean(key: 'remember'),
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
