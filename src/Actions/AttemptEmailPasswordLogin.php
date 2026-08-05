<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Cache\RateLimiter;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;
use Simtabi\Laranail\Auth\Contracts\AttemptEmailPasswordLoginInterface;

class AttemptEmailPasswordLogin implements AttemptEmailPasswordLoginInterface
{
    public function __construct(
        private AuthFactory $auth,
        private RateLimiter $limiter,
    ) {
    }

    public function execute(AttemptEmailPasswordLoginInput $input): AuthResult
    {
        $key = 'login:' . $input->guard . ':' . mb_strtolower(string: $input->email);
        $maxAttempts = (int) config(key: 'auth-kit.rate_limit.max_attempts', default: 5);
        $decaySeconds = (int) config(key: 'auth-kit.rate_limit.decay_minutes', default: 1) * 60;

        if ($this->limiter->tooManyAttempts(key: $key, maxAttempts: $maxAttempts)) {
            return AuthResult::throttled(retryAfterSeconds: $this->limiter->availableIn(key: $key));
        }

        $guard = $this->auth->guard(name: $input->guard);

        $ok = $guard->attempt(
            credentials: ['email' => $input->email, 'password' => $input->password],
            remember: $input->remember,
        );

        if (! $ok) {
            $this->limiter->hit(key: $key, decaySeconds: $decaySeconds);

            return AuthResult::failed();
        }

        $this->limiter->clear(key: $key);

        return AuthResult::passed(user: $guard->user());
    }
}
