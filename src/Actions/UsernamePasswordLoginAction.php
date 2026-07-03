<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Simtabi\Laranail\Auth\Support\AuthConfig;
use Simtabi\Laranail\Auth\Events\UsernamePasswordLoginFailed;
use Simtabi\Laranail\Auth\Events\UsernamePasswordLoginSuccess;
use Simtabi\Laranail\Auth\Events\UsernamePasswordLoginThrottled;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class UsernamePasswordLoginAction
{
    public function __construct(
        private ?string $guard = null,
        private int     $maxAttempts = 5,
        private int     $decaySeconds = 60,
    ) {
    }

    /**
     * @param array{username: string, password: string} $credentials
     */
    public function handle(array $credentials, bool $remember = false): bool
    {
        $guard = AuthConfig::fromGuard(guard: $this->guard)->guard;
        $username = $credentials['username'];
        $ip = request()->ip();
        $key = mb_strtolower(string: $username) . '|' . $ip . '|' . $guard;

        if (RateLimiter::tooManyAttempts(key: $key, maxAttempts: $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn(key: $key);

            Event::dispatch(
                event: new UsernamePasswordLoginThrottled(username: $username, guard: $guard, seconds: $seconds)
            );

            throw new TooManyRequestsHttpException(retryAfter: $seconds);
        }

        if (!Auth::guard(name: $guard)->attempt(credentials: $credentials, remember: $remember)) {
            RateLimiter::hit(key: $key, decaySeconds: $this->decaySeconds);

            Event::dispatch(
                event: new UsernamePasswordLoginFailed(username: $username, guard: $guard)
            );

            return false;
        }

        RateLimiter::clear(key: $key);

        Event::dispatch(
            event: new UsernamePasswordLoginSuccess(username: $username, guard: $guard)
        );

        return true;
    }
}
