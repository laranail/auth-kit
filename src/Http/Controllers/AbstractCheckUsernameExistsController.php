<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Actions\CheckUsernameExists;
use Simtabi\Laranail\Auth\Dtos\CheckUsernameExistsInput;
use Simtabi\Laranail\Auth\Http\Requests\CheckUsernameExistsRequest;

abstract class AbstractCheckUsernameExistsController extends AbstractAuthController
{
    public function __invoke(CheckUsernameExistsRequest $request, CheckUsernameExists $action): mixed
    {
        $exists = $action->execute(
            input: new CheckUsernameExistsInput(
                username: $request->validated(key: 'username'),
                guard: $this->guard(),
            )
        );

        if ($request->expectsJson()) {
            return response()->json(data: ['exists' => $exists]);
        }

        return $this->respond(request: $request, exists: $exists);
    }

    abstract protected function respond(Request $request, bool $exists): mixed;
}
