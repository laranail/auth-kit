<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Support\AuthResult;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Dtos\SocialCallbackActionInput;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Simtabi\Laranail\Auth\Contracts\SocialCallbackActionInterface;

class SocialCallbackAction implements SocialCallbackActionInterface
{
    public function __construct(
        private SocialiteFactory $socialite,
    ) {
    }

    public function execute(SocialCallbackActionInput $input): AuthResult
    {
        $socialiteUser = $this->socialite->driver($input->provider->value)->user();

        $user = ($input->resolve)($socialiteUser);

        if (! $user instanceof Authenticatable) {
            return AuthResult::failed();
        }

        return AuthResult::passed($user);
    }
}
