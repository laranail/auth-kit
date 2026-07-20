<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Support\AuthResult;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Dtos\AttemptEmailPasswordLoginInput;
use Simtabi\Laranail\Auth\Contracts\AttemptEmailPasswordLoginInterface;

class AttemptEmailPasswordLogin implements AttemptEmailPasswordLoginInterface
{
    public function __construct(
        private AuthFactory $auth,
    ) {
    }

    public function execute(AttemptEmailPasswordLoginInput $input): AuthResult
    {
        $guard = $this->auth->guard($input->guard);

        $ok = $guard->attempt(
            ['email' => $input->email, 'password' => $input->password],
            $input->remember,
        );

        if (! $ok) {
            return AuthResult::failed();
        }

        return AuthResult::passed($guard->user());
    }
}
