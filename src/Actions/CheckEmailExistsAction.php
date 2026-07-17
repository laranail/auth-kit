<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Dtos\CheckEmailExistsInput;

class CheckEmailExistsAction
{
    public function __construct(
        private AuthFactory $auth,
    ) {
    }

    public function execute(CheckEmailExistsInput $input): bool
    {
        $guardName = $input->guard ?? config('auth-kit.guard');

        $provider = $this->auth->guard($guardName)->getProvider();

        return $provider->retrieveByCredentials(['email' => $input->email]) !== null;
    }
}
