<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

abstract class AbstractAuthController
{
    protected function guard(): string
    {
        return config(key: 'auth-kit.guard');
    }
}
