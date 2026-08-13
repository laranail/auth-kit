<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Support\AuthKit;
use Simtabi\Laranail\Auth\Contracts\LogoutUserInterface;

abstract class AbstractLogoutController extends AbstractAuthController
{
    public function __invoke(Request $request, LogoutUserInterface $action): mixed
    {
        $action->execute(guard: $this->guard());

        if ($request->expectsJson()) {
            return $this->jsonResponse(status: 'passed', data: ['message' => 'Logged out successfully.']);
        }

        return $this->loggedOut(request: $request);
    }

    protected function loggedOut(Request $request): mixed
    {
        return redirect()->to(path: AuthKit::afterLogoutRedirect());
    }
}
