<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Simtabi\Laranail\Auth\Support\AuthKit;
use Illuminate\Contracts\Auth\PasswordBroker;

abstract class AbstractPasswordResetLinkController extends AbstractAuthController
{
    public function store(Request $request): mixed
    {
        $request->validate([
            'email'                                                                   => 'required|email',
            config(key: 'auth-kit.turnstile.input', default: 'cf-turnstile-response') => AuthKit::turnstileRules(),
        ]);

        $email = $request->input('email');
        if (config('fortify.lowercase_usernames')) {
            $email = Str::lower($email);
        }

        $status = $this->broker()->sendResetLink(['email' => $email]);

        if ($request->expectsJson()) {
            return $status === Password::RESET_LINK_SENT
                ? $this->jsonResponse(status: 'passed', data: ['message' => __($status)])
                : $this->jsonResponse(status: 'failed', data: ['message' => __($status)], code: 422);
        }

        return $status === Password::RESET_LINK_SENT
            ? $this->sent(request: $request, status: $status)
            : $this->failed(request: $request, status: $status);
    }

    protected function sent(Request $request, string $status): mixed
    {
        return back()->with('status', __($status));
    }

    protected function failed(Request $request, string $status): mixed
    {
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    protected function broker(): PasswordBroker
    {
        return Password::broker(config('fortify.passwords'));
    }
}
