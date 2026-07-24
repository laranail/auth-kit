<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

interface LogoutUserInterface
{
    public function execute(?string $guard = null): void;
}
