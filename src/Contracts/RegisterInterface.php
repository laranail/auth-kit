<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Dtos\RegisterInput;
use Illuminate\Contracts\Auth\Authenticatable;

interface RegisterInterface
{
    public function execute(RegisterInput $input): Authenticatable;
}
