<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;

interface ResolveSocialIdentityInterface
{
    public function execute(SocialProvider $provider, SocialiteUser $socialUser, string $guard): ?Authenticatable;
}
