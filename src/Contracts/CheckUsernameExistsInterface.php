<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Dtos\CheckUsernameExistsInput;

interface CheckUsernameExistsInterface
{
    public function execute(CheckUsernameExistsInput $input): bool;
}
