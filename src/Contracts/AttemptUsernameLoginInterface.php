<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\Auth\Dtos\AttemptUsernameLoginInput;

interface AttemptUsernameLoginInterface
{
    public function execute(AttemptUsernameLoginInput $input): AuthResult;
}
