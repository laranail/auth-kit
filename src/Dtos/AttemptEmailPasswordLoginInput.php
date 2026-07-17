<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

class AttemptEmailPasswordLoginInput
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
        public ?string $guard = null,
    ) {
    }
}
