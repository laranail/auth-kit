<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Support\SocialRedirectResult;
use Simtabi\Laranail\Auth\Dtos\SocialRedirectActionInput;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Simtabi\Laranail\Auth\Contracts\SocialRedirectActionInterface;

class SocialRedirectAction implements SocialRedirectActionInterface
{
    public function __construct(
        private SocialiteFactory $socialite,
    ) {
    }

    public function execute(SocialRedirectActionInput $input): SocialRedirectResult
    {
        $driver = $this->socialite->driver($input->provider->value);

        if ($input->state !== null && method_exists($driver, 'state')) {
            $driver->state($input->state);
        }

        return new SocialRedirectResult(
            url: $driver->redirect()->getTargetUrl(),
            state: $input->state,
        );
    }
}
