<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dtos;

use Illuminate\Contracts\Auth\Authenticatable;

class IssueTokenForUserInput
{
    public function __construct(
        public Authenticatable $user,
        public ?string $name = null,
        public array $abilities = ['*'],
    ) {
    }
}
