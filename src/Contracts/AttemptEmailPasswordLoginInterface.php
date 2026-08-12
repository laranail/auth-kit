<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Support\AuthResult;

interface AttemptEmailPasswordLoginInterface
{
    public function execute(Request $request, string $guard): AuthResult;
}
