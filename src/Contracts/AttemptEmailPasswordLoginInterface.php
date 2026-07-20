<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;

interface AttemptEmailPasswordLoginInterface
{
    public function execute(AttemptEmailPasswordLoginInput $input): AuthResult;
}
