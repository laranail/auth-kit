<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Results;

use Illuminate\Contracts\Auth\Authenticatable;

enum AuthStatus: string
{
    case Passed    = 'passed';
    case Failed    = 'failed';
    case Throttled = 'throttled';
}

final readonly class AuthResult
{
    private function __construct(
        public AuthStatus $status,
        public ?Authenticatable $user = null,
        public ?int $retryAfterSeconds = null,
    ) {
    }

    public static function passed(Authenticatable $user): self
    {
        return new self(AuthStatus::Passed, user: $user);
    }

    public static function failed(): self
    {
        return new self(AuthStatus::Failed);
    }

    public static function throttled(int $retryAfterSeconds): self
    {
        return new self(AuthStatus::Throttled, retryAfterSeconds: $retryAfterSeconds);
    }

    public function isPassed(): bool
    {
        return $this->status === AuthStatus::Passed;
    }
}
