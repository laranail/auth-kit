<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Guards;

use Illuminate\Http\Request;
use Illuminate\Auth\SessionGuard;
use Illuminate\Auth\Events\Failed;
use Simtabi\Laranail\Auth\AuthManager;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\StatefulGuard;

class LaranailGuard extends SessionGuard implements StatefulGuard
{
    protected AuthManager $authManager;

    public function __construct(
        string $name,
        UserProvider $provider,
        Session $session,
        Request $request,
        AuthManager $authManager,
    ) {
        parent::__construct($name, $provider, $session, $request);

        $this->authManager = $authManager;
    }

    /**
     * Attempt to authenticate using a named auth method.
     */
    public function attemptWith(string $method, array $credentials, bool $remember = false): bool
    {
        $authMethod = $this->authManager->method($method);

        if (! $authMethod) {
            return false;
        }

        if (! $authMethod->canHandle($this->request)) {
            return false;
        }

        if (! $authMethod->validate($this->request)) {
            $this->fireFailedEvent(null, $credentials);

            return false;
        }

        $user = $authMethod->authenticate($this->request);

        if ($user) {
            $this->login($user, $remember);
            $this->fireAuthenticatedEvent($user);

            return true;
        }

        $this->fireFailedEvent(null, $credentials);

        return false;
    }

    protected function fireAuthenticatedEvent($user): void
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Authenticated($this->name, $user));
        }
    }

    protected function fireFailedEvent($user, array $credentials): void
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Failed($this->name, $user, $credentials));
        }
    }
}
