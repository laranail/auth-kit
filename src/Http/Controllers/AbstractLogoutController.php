<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Actions\LogoutUser;

abstract class AbstractLogoutController extends AbstractAuthController
{
    public function __invoke(Request $request, LogoutUser $action): mixed
    {
        $action->execute(guard: $this->guard());

        return $this->loggedOut(request: $request);
    }

    abstract protected function loggedOut(Request $request): mixed;
}
