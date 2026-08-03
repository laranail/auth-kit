<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Models\PendingEmailToken;
use Simtabi\Laranail\Auth\Dtos\VerifyPendingEmailTokenInput;
use Simtabi\Laranail\Auth\Contracts\VerifyPendingEmailTokenInterface;

class VerifyPendingEmailToken implements VerifyPendingEmailTokenInterface
{
    public function execute(VerifyPendingEmailTokenInput $input): ?PendingEmailToken
    {
        $pendingToken = PendingEmailToken::active()
            ->where(column: 'email', operator: '=', value: $input->email)
            ->first();

        if (! $pendingToken) {
            return null;
        }

        if ($pendingToken->token !== $input->token) {
            return null;
        }

        return $pendingToken;
    }
}
