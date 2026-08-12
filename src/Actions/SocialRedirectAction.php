<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Simtabi\Laranail\Auth\Support\SocialRedirectResult;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Simtabi\Laranail\Auth\Contracts\SocialRedirectActionInterface;

class SocialRedirectAction implements SocialRedirectActionInterface
{
    public function __construct(
        private SocialiteFactory $socialite,
    ) {
    }

    public function execute(Request $request): SocialRedirectResult
    {
        $provider = SocialProvider::from(value: $request->route('provider'));
        $state = $request->query('state');

        $driver = $this->socialite->driver($provider->value);

        if ($state !== null && method_exists($driver, 'state')) {
            $driver->state($state);
        }

        return new SocialRedirectResult(
            url: $driver->redirect()->getTargetUrl(),
            state: $state,
        );
    }
}
