<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\Auth\Dtos\EnforceLoginRateLimitInput;

interface EnforceLoginRateLimitInterface
{
    public function execute(EnforceLoginRateLimitInput $input): AuthResult;
}
