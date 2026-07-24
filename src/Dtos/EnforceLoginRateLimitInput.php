<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

class EnforceLoginRateLimitInput
{
    public function __construct(
        public string $key,
        public string $guard,
    ) {
    }
}
