<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions\Email;

final readonly class CheckEmailExistsInput
{
    public function __construct(
        public string $email,
        public ?string $guard = null,
    ) {
    }
}
