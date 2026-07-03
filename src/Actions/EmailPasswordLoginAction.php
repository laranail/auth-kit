<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Simtabi\Laranail\Auth\Support\AuthConfig;
use Simtabi\Laranail\Auth\Events\EmailPasswordLoginFailed;
use Simtabi\Laranail\Auth\Events\EmailPasswordLoginSuccess;
use Simtabi\Laranail\Auth\Events\EmailPasswordLoginThrottled;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class EmailPasswordLoginAction
{
    public function __construct(
        private ?string $guard = null,
        private int $maxAttempts = 5,
        private int $decaySeconds = 60,
    ) {
    }

    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function handle(array $credentials, bool $remember = false): bool
    {
        $auth = AuthConfig::fromGuard($this->guard);
        $guard = $auth->guard;
        $email = $credentials['email'];
        $ip = request()->ip() ?? '127.0.0.1';
        $key = mb_strtolower($email).'|'.$ip.'|'.$guard;

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            Event::dispatch(new EmailPasswordLoginThrottled(
                email: $email,
                guard: $guard,
                seconds: $seconds,
            ));

            throw new TooManyRequestsHttpException($seconds);
        }

        if (! Auth::guard($guard)->attempt($credentials, $remember)) {
            RateLimiter::hit($key, $this->decaySeconds);

            Event::dispatch(new EmailPasswordLoginFailed(
                email: $email,
                guard: $guard,
            ));

            return false;
        }

        RateLimiter::clear($key);

        Event::dispatch(new EmailPasswordLoginSuccess(
            email: $email,
            guard: $guard,
        ));

        return true;
    }
}
