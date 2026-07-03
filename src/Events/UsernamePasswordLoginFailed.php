<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Events;

class UsernamePasswordLoginFailed
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $username,
        public readonly string $guard,
    ) {
        //
    }
}
