<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Contracts\CheckEmailExistsInterface;
use Simtabi\Laranail\Auth\Http\Requests\CheckEmailExistsRequest;

abstract class AbstractCheckEmailExistsController extends AbstractAuthController
{
    public function __invoke(CheckEmailExistsRequest $request, CheckEmailExistsInterface $action): mixed
    {
        $exists = $action->execute(
            request: $request,
            guard: $this->guard(),
        );

        if ($request->expectsJson()) {
            return response()->json(data: ['exists' => $exists]);
        }

        return $this->respond(request: $request, exists: $exists);
    }

    abstract protected function respond(Request $request, bool $exists): mixed;
}
