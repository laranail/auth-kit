<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Simtabi\Laranail\Auth\Contracts\LogoutUserInterface;

class LogoutUser implements LogoutUserInterface
{
    public function __construct(
        private AuthFactory $auth,
        private Session $session,
    ) {
    }

    public function execute(?string $guard = null): void
    {
        $guardName = $guard ?? config('auth-kit.guard');

        $this->auth->guard($guardName)->logout();
        $this->session->invalidate();
        $this->session->regenerateToken();
    }
}
