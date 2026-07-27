<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

use Closure;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialCallbackActionInput
{
    /**
     * @param  SocialProvider  $provider
     * @param  Closure(SocialiteUser): \Illuminate\Contracts\Auth\Authenticatable|null  $resolve
     */
    public function __construct(
        public SocialProvider $provider,
        public Closure $resolve,
    ) {
    }
}
