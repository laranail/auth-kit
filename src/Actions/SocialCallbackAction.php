<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
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

    public function execute(Request $request, string $guard): AuthResult
    {
        $provider = SocialProvider::from(value: $request->route('provider'));
        $socialiteUser = $this->socialite->driver($provider->value)->user();

        $user = $this->resolver->execute(
            provider: $provider,
            socialUser: $socialiteUser,
            guard: $guard,
        );

        if (! $user instanceof Authenticatable) {
            return AuthResult::failed();
        }

        return AuthResult::passed($user);
    }
}
