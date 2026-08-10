<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Closure;
use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Enums\AuthStatus;
use Simtabi\Laranail\Auth\Support\AuthResult;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Simtabi\Laranail\Auth\Dtos\SocialCallbackActionInput;
use Simtabi\Laranail\Auth\Contracts\SocialCallbackActionInterface;

abstract class AbstractSocialCallbackController extends AbstractAuthController
{
    public function __invoke(Request $request, SocialCallbackActionInterface $action): mixed
    {
        $provider = SocialProvider::from(value: $request->route('provider'));

        $result = $action->execute(
            input: new SocialCallbackActionInput(
                provider: $provider,
                resolve: $this->resolve(provider: $provider),
            )
        );

        if ($request->expectsJson()) {
            return match ($result->status) {
                AuthStatus::Passed => response()->json(data: [
                    'status' => 'passed',
                    'user'   => $result->user,
                ]),
                AuthStatus::Failed => response()->json(data: [
                    'status'  => 'failed',
                    'message' => 'Social authentication failed.',
                ], status: 422),
                default => response()->json(data: [
                    'status'  => 'failed',
                    'message' => 'Social authentication failed.',
                ], status: 422),
            };
        }

        return match ($result->status) {
            AuthStatus::Passed => $this->passed(request: $request, result: $result),
            AuthStatus::Failed => $this->failed(request: $request, result: $result),
            default            => $this->failed(request: $request, result: $result),
        };
    }

    /**
     * Consumer provides find-or-create logic.
     *
     * @param  SocialProvider  $provider
     * @return Closure(\Laravel\Socialite\Contracts\User): \Illuminate\Contracts\Auth\Authenticatable|null
     */
    abstract protected function resolve(SocialProvider $provider): Closure;

    abstract protected function passed(Request $request, AuthResult $result): mixed;

    abstract protected function failed(Request $request, AuthResult $result): mixed;
}
