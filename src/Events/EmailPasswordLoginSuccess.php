<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Events;

class EmailPasswordLoginSuccess
{
    public function __construct(
        public readonly string $email,
        public readonly string $guard,
    ) {
    }
}
