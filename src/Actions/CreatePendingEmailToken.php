<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Support\Str;
use Simtabi\Laranail\Auth\Models\PendingEmailToken;
use Simtabi\Laranail\Auth\Dtos\CreatePendingEmailTokenInput;
use Simtabi\Laranail\Auth\Contracts\CreatePendingEmailTokenInterface;

class CreatePendingEmailToken implements CreatePendingEmailTokenInterface
{
    public function execute(CreatePendingEmailTokenInput $input): PendingEmailToken
    {
        $token = Str::random(
            length: config()->integer(key: 'auth-kit.registration.token_length', default: 40)
        );

        return PendingEmailToken::updateOrCreate(
            attributes: ['email' => $input->email],
            values: [
                'token'      => $token,
                'expires_at' => now()->addMinutes(
                    config()->integer(key: 'auth-kit.registration.token_ttl_minutes', default: 60)
                ),
            ]
        );
    }
}
