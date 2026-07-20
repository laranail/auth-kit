<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Dtos\FindUserByUsernameInput;
use Simtabi\Laranail\Auth\Contracts\FindUserByUsernameInterface;

class FindUserByUsername implements FindUserByUsernameInterface
{
    public function __construct(
        private AuthFactory $auth,
    ) {
    }

    public function execute(FindUserByUsernameInput $input): ?Authenticatable
    {
        $provider = $this->auth->guard($input->guard)->getProvider();

        return $provider->retrieveByCredentials(['username' => $input->username]);
    }
}
