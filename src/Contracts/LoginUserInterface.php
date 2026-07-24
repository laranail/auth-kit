<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface LoginUserInterface
{
    public function execute(Authenticatable $user, string $guard, bool $remember = false): void;
}
