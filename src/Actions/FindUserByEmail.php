<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Dtos\FindUserByEmailInput;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Contracts\FindUserByEmailInterface;

class FindUserByEmail implements FindUserByEmailInterface
{
    public function __construct(
        private AuthFactory $auth,
    ) {
    }

    public function execute(FindUserByEmailInput $input): ?Authenticatable
    {
        $provider = $this->auth->guard($input->guard)->getProvider();

        return $provider->retrieveByCredentials(['email' => $input->email]);
    }
}
