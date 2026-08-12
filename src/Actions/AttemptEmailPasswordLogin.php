<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Contracts\AttemptEmailPasswordLoginInterface;

class AttemptEmailPasswordLogin implements AttemptEmailPasswordLoginInterface
{
    public function __construct(
        private AuthFactory $auth,
        private RateLimiter $limiter,
    ) {
    }

    public function execute(Request $request, string $guard): AuthResult
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $remember = (bool) $request->input('remember', default: false);
        $ip = $request->ip();

        $key = 'login:' . $guard . ':' . mb_strtolower(string: $email) . ':' . ($ip ?? '_');
        $maxAttempts = (int) config(key: 'auth-kit.rate_limit.max_attempts', default: 5);
        $decaySeconds = (int) config(key: 'auth-kit.rate_limit.decay_minutes', default: 1) * 60;

        if ($this->limiter->tooManyAttempts(key: $key, maxAttempts: $maxAttempts)) {
            return AuthResult::throttled(retryAfterSeconds: $this->limiter->availableIn(key: $key));
        }

        $guardInstance = $this->auth->guard(name: $guard);

        $ok = $guardInstance->attempt(
            credentials: ['email' => $email, 'password' => $password],
            remember: $remember,
        );

        if (! $ok) {
            $this->limiter->hit(key: $key, decaySeconds: $decaySeconds);

            return AuthResult::failed();
        }

        $this->limiter->clear(key: $key);

        return AuthResult::passed(user: $guardInstance->user());
    }
}
