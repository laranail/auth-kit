<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Models\Social;
use Simtabi\Laranail\Auth\Dtos\CreateSocialAccountActionInput;

interface CreateSocialAccountActionInterface
{
    public function execute(CreateSocialAccountActionInput $input): Social;
}
