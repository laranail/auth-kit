<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Models\PendingEmailToken;
use Simtabi\Laranail\Auth\Dtos\CreatePendingEmailTokenInput;

interface CreatePendingEmailTokenInterface
{
    public function execute(CreatePendingEmailTokenInput $input): PendingEmailToken;
}
