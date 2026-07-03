<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Events;

class UsernamePasswordLoginSuccess
{
    public function __construct(
        public readonly string $username,
        public readonly string $guard,
    ) {
    }
}
