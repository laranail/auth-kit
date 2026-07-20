<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Dtos\CheckEmailExistsInput;

interface CheckEmailExistsInterface
{
    public function execute(CheckEmailExistsInput $input): bool;
}
