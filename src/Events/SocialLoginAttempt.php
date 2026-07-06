<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SocialLoginAttempt
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly string $provider,
        public readonly string $providerId,
        public readonly string $guard,
    ) {
    }
}
