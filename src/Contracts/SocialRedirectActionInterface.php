<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Support\SocialRedirectResult;

interface SocialRedirectActionInterface
{
    public function execute(Request $request): SocialRedirectResult;
}
