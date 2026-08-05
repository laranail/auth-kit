<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

class CreateNewUserInput
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $passwordConfirmation,
        public string $guard,
    ) {
    }
}
