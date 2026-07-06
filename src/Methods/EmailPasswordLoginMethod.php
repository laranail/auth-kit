<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Methods;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Simtabi\Laranail\Auth\Support\AuthConfig;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Events\EmailPasswordLoginFailed;
use Simtabi\Laranail\Auth\Events\EmailPasswordLoginSuccess;
use Simtabi\Laranail\Auth\Events\EmailPasswordLoginThrottled;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class EmailPasswordLoginMethod extends BaseAuthMethod
{
    public function __construct(
        protected ?string $guard = null,
        protected int $maxAttempts = 5,
        protected int $decaySeconds = 60,
    ) {
    }

    public function getName(): string
    {
        return 'email';
    }

    public function canHandle(Request $request): bool
    {
        return $request->has(['email', 'password']);
    }

    public function validate(Request $request): bool
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        return true;
    }

    public function authenticate(Request $request): ?Authenticatable
    {
        $credentials = $request->only(['email', 'password']);

        if (Auth::guard($this->resolveGuard())->attempt($credentials, $request->boolean('remember'))) {
            return Auth::guard($this->resolveGuard())->user();
        }

        return null;
    }

    public function getConfig(): array
    {
        return [
            'guard'        => $this->guard,
            'maxAttempts'  => $this->maxAttempts,
            'decaySeconds' => $this->decaySeconds,
        ];
    }

    /**
     * Handle an email/password login with rate limiting and events.
     *
     * @param  array{email: string, password: string}  $credentials
     */
    public function handle(array $credentials, bool $remember = false): bool
    {
        $guard = $this->resolveGuard();
        $email = $credentials['email'];
        $ip = request()->ip() ?? '127.0.0.1';
        $key = mb_strtolower($email).'|'.$ip.'|'.$guard;

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            Event::dispatch(new EmailPasswordLoginThrottled(
                email: $email,
                guard: $guard,
                seconds: $seconds,
            ));

            throw new TooManyRequestsHttpException($seconds);
        }

        if (! Auth::guard($guard)->attempt($credentials, $remember)) {
            RateLimiter::hit($key, $this->decaySeconds);

            Event::dispatch(new EmailPasswordLoginFailed(
                email: $email,
                guard: $guard,
            ));

            return false;
        }

        RateLimiter::clear($key);

        Event::dispatch(new EmailPasswordLoginSuccess(
            email: $email,
            guard: $guard,
        ));

        return true;
    }

    private function resolveGuard(): string
    {
        return AuthConfig::fromGuard($this->guard)->guard;
    }
}
