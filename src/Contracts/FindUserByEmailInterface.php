<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Dtos\FindUserByEmailInput;

interface FindUserByEmailInterface
{
    public function execute(FindUserByEmailInput $input): ?Authenticatable;
}
