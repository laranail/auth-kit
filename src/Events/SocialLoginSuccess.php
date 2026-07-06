<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SocialLoginSuccess
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Authenticatable $user,
        public readonly string $provider,
        public readonly string $providerId,
        public readonly bool $isNewUser,
        public readonly string $guard,
    ) {
    }
}
