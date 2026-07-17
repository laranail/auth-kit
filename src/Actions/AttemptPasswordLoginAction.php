<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Support\AuthResult;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Dtos\AttemptPasswordLoginInput;

class AttemptPasswordLoginAction
{
    public function __construct(
        private AuthFactory $auth,
    ) {
    }

    public function execute(AttemptPasswordLoginInput $input): AuthResult
    {
        $guardName = $input->guard ?? config('auth-kit.guard');
        $guard = $this->auth->guard($guardName);

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
