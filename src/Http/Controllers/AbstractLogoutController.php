<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Contracts\LogoutUserInterface;

abstract class AbstractLogoutController extends AbstractAuthController
{
    public function __invoke(Request $request, LogoutUserInterface $action): mixed
    {
        $action->execute(guard: $this->guard());

        return $this->loggedOut(request: $request);
    }

    abstract protected function loggedOut(Request $request): mixed;
}
