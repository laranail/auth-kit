<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class CreateSocialAccountActionInput
{
    public function __construct(
        public Authenticatable $authenticatable,
        public SocialProvider $provider,
        public SocialiteUser $socialUser,
    ) {
    }
}
