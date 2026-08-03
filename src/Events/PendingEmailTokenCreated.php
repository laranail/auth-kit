<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PendingEmailTokenCreated
{
    use Dispatchable;

    public function __construct(
        public string $email,
        public string $token,
    ) {
    }
}
