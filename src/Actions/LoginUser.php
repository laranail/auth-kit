<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class LoginUser
{
    public function __construct(
        private AuthFactory $auth,
        private Session $session,
    ) {
    }

    public function execute(Authenticatable $user, bool $remember = false, ?string $guard = null): void
    {
        $guardName = $guard ?? config('auth-kit.guard');

        $this->auth->guard($guardName)->login($user, $remember);
        $this->session->regenerate();
    }
}
