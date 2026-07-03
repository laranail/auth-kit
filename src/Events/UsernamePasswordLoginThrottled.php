<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Events;

class UsernamePasswordLoginThrottled
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $username,
        public readonly string $guard,
        public readonly int $seconds,
    ) {
        //
    }
}
