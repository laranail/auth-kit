<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

use Simtabi\Laranail\Auth\Enums\SocialProvider;

class SocialRedirectActionInput
{
    public function __construct(
        public SocialProvider $provider,
        public ?string $state = null,
    ) {
    }
}
