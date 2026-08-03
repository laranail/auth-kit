<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Models\PendingEmailToken;
use Simtabi\Laranail\Auth\Actions\CreatePendingEmailToken;
use Simtabi\Laranail\Auth\Dtos\CreatePendingEmailTokenInput;
use Simtabi\Laranail\Auth\Http\Requests\CreatePendingEmailTokenRequest;

abstract class AbstractCreatePendingEmailTokenController extends AbstractAuthController
{
    public function __invoke(CreatePendingEmailTokenRequest $request, CreatePendingEmailToken $action): mixed
    {
        $pendingToken = $action->execute(
            input: new CreatePendingEmailTokenInput(
                email: $request->validated(key: 'email'),
            )
        );

        if ($request->expectsJson()) {
            return response()->json(data: [
                'pending_token' => $pendingToken->token,
                'email'         => $pendingToken->email,
                'expires_at'    => $pendingToken->expires_at->toISOString(),
            ]);
        }

        return $this->created(request: $request, pendingToken: $pendingToken);
    }

    abstract protected function created(Request $request, PendingEmailToken $pendingToken): mixed;
}
