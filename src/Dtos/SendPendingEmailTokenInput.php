<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

class SendPendingEmailTokenInput
{
    public function __construct(
        public string $email,
    ) {
    }
}
