<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Auth\Events\UserLoggedIn;

class IssueSession
{
    /**
     * Create an authenticated session for the user.
     */
    public function handle(Request $request, Model $user): void
    {
        Auth::login($user);

        $request->session()->regenerate();

        event(new UserLoggedIn(
            user: $user,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));
    }
}
