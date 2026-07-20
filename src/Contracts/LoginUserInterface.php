<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface LoginUserInterface
{
    public function execute(Authenticatable $user, bool $remember = false, ?string $guard = null): void;
}
