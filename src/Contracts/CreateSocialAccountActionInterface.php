<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Models\Social;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;

interface CreateSocialAccountActionInterface
{
    public function execute(Authenticatable $authenticatable, SocialProvider $provider, SocialiteUser $socialUser): Social;
}
