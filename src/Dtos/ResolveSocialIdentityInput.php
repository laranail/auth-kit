<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class ResolveSocialIdentityInput
{
    public function __construct(
        public SocialProvider $provider,
        public SocialiteUser $socialUser,
        public string $guard,
    ) {
    }
}
