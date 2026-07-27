<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Support\SocialRedirectResult;
use Simtabi\Laranail\Auth\Dtos\SocialRedirectActionInput;

interface SocialRedirectActionInterface
{
    public function execute(SocialRedirectActionInput $input): SocialRedirectResult;
}
