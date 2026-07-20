<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Support\AuthResult;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Dtos\AttemptUsernameLoginInput;
use Simtabi\Laranail\Auth\Contracts\AttemptUsernameLoginInterface;

class AttemptUsernameLogin implements AttemptUsernameLoginInterface
{
    public function __construct(
        private AuthFactory $auth,
    ) {
    }

    public function execute(AttemptUsernameLoginInput $input): AuthResult
    {
        $guard = $this->auth->guard($input->guard);

        $ok = $guard->attempt(
            ['username' => $input->username, 'password' => $input->password],
            $input->remember,
        );

        if (! $ok) {
            return AuthResult::failed();
        }

        return AuthResult::passed($guard->user());
    }
}
