<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Dtos\FindUserByUsernameInput;

interface FindUserByUsernameInterface
{
    public function execute(FindUserByUsernameInput $input): ?Authenticatable;
}
