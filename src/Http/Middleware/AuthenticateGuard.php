<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        if (auth()->guard(name: $guard)->check()) {
            return $next($request);
        }

        return redirect()->guest(path: route(name: 'login.create'));
    }
}
