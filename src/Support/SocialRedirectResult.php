<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Support;

class SocialRedirectResult
{
    public function __construct(
        public string $url,
    ) {
    }
}
