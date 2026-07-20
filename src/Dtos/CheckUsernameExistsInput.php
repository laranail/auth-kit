<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

class CheckUsernameExistsInput
{
    public function __construct(
        public string $username,
        public string $guard,
    ) {
    }
}
