<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Events;

use Illuminate\Foundation\Auth\User as Authenticatable;

class UserLoggedIn
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Authenticatable $user,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {
    }
}
