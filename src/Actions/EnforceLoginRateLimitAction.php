<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Cache\RateLimiter;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\Auth\Dtos\EnforceLoginRateLimitInput;
use Simtabi\Laranail\Auth\Contracts\EnforceLoginRateLimitInterface;

class EnforceLoginRateLimitAction implements EnforceLoginRateLimitInterface
{
    public function __construct(
        private RateLimiter $limiter,
    ) {
    }

    public function execute(EnforceLoginRateLimitInput $input): AuthResult
    {
        $key = 'login:' . $input->guard . ':' . $input->key;
        $maxAttempts = config('auth-kit.rate_limit.max_attempts', 5);
        $decayMinutes = config('auth-kit.rate_limit.decay_minutes', 1);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return AuthResult::throttled(
                $this->limiter->availableIn($key),
            );
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        return AuthResult::allowed();
    }
}
