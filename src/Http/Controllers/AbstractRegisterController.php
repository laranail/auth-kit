<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\CreatesNewUsers as FortifyCreateNewUser;

abstract class AbstractRegisterController extends AbstractAuthController
{
    abstract protected function registered(Request $request, Authenticatable $user): mixed;

    public function store(Request $request, FortifyCreateNewUser $creator): mixed
    {
        event(new Registered($user = $creator->create($request->all())));

        return $this->registered($request, $user);
    }
}
