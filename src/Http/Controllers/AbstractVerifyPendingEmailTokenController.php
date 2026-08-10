<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Models\PendingEmailToken;
use Simtabi\Laranail\Auth\Dtos\VerifyPendingEmailTokenInput;
use Simtabi\Laranail\Auth\Contracts\VerifyPendingEmailTokenInterface;
use Simtabi\Laranail\Auth\Http\Requests\VerifyPendingEmailTokenRequest;

abstract class AbstractVerifyPendingEmailTokenController extends AbstractAuthController
{
    public function __invoke(VerifyPendingEmailTokenRequest $request, VerifyPendingEmailTokenInterface $action): mixed
    {
        $pendingToken = $action->execute(
            input: new VerifyPendingEmailTokenInput(
                email: $request->validated(key: 'email'),
                token: $request->validated(key: 'token'),
            )
        );

        if ($request->expectsJson()) {
            if (! $pendingToken) {
                return response()->json(data: [
                    'status'  => 'failed',
                    'message' => 'Invalid or expired token.',
                ], status: 422);
            }

            return response()->json(data: [
                'status' => 'verified',
                'email'  => $pendingToken->email,
            ]);
        }

        if (! $pendingToken) {
            return $this->failed(request: $request);
        }

        return $this->verified(request: $request, pendingToken: $pendingToken);
    }

    abstract protected function verified(Request $request, PendingEmailToken $pendingToken): mixed;

    abstract protected function failed(Request $request): mixed;
}
