<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Support\AuthResult;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Dtos\SocialCallbackActionInput;
use Simtabi\Laranail\Auth\Dtos\ResolveSocialIdentityInput;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Simtabi\Laranail\Auth\Contracts\SocialCallbackActionInterface;
use Simtabi\Laranail\Auth\Contracts\ResolveSocialIdentityInterface;

class SocialCallbackAction implements SocialCallbackActionInterface
{
    public function __construct(
        private SocialiteFactory $socialite,
        private ResolveSocialIdentityInterface $resolver,
    ) {
    }

    public function execute(SocialCallbackActionInput $input): AuthResult
    {
        $socialiteUser = $this->socialite->driver($input->provider->value)->user();

        $user = $this->resolver->execute(new ResolveSocialIdentityInput(
            provider: $input->provider,
            socialUser: $socialiteUser,
            guard: $input->guard,
        ));

        if (! $user instanceof Authenticatable) {
            return AuthResult::failed();
        }

        return AuthResult::passed($user);
    }
}
