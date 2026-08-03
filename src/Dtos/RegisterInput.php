<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

class RegisterInput
{
    public function __construct(
        public string $email,
        public string $password,
        public string $firstName,
        public ?string $lastName = null,
        public ?string $username = null,
        public string $guard,
    ) {
    }
}
