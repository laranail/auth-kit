<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Events;

class EmailPasswordLoginThrottled
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $email,
        public readonly string $guard,
        public readonly int $seconds,
    ) {
        //
    }
}
