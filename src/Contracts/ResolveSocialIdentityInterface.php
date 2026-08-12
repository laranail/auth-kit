<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Dtos\ResolveSocialIdentityInput;

interface ResolveSocialIdentityInterface
{
    public function execute(ResolveSocialIdentityInput $input): ?Authenticatable;
}
