<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

class AttemptUsernameLoginInput
{
    public function __construct(
        public string $username,
        public string $password,
        public string $guard,
        public bool $remember = false,
    ) {
    }
}
