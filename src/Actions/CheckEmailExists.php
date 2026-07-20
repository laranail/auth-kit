<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Dtos\CheckEmailExistsInput;
use Simtabi\Laranail\Auth\Contracts\CheckEmailExistsInterface;

class CheckEmailExists implements CheckEmailExistsInterface
{
    public function __construct(
        private AuthFactory $auth,
    ) {
    }

    public function execute(CheckEmailExistsInput $input): bool
    {
        $provider = $this->auth->guard($input->guard)->getProvider();

        return $provider->retrieveByCredentials(['email' => $input->email]) !== null;
    }
}
