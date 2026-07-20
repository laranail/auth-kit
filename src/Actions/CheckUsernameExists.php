<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Dtos\CheckUsernameExistsInput;
use Simtabi\Laranail\Auth\Contracts\CheckUsernameExistsInterface;

class CheckUsernameExists implements CheckUsernameExistsInterface
{
    public function __construct(
        private AuthFactory $auth,
    ) {
    }

    public function execute(CheckUsernameExistsInput $input): bool
    {
        $provider = $this->auth->guard($input->guard)->getProvider();

        return $provider->retrieveByCredentials(['username' => $input->username]) !== null;
    }
}
