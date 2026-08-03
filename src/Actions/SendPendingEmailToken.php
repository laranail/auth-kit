<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Models\PendingEmailToken;
use Simtabi\Laranail\Auth\Dtos\SendPendingEmailTokenInput;
use Simtabi\Laranail\Auth\Events\PendingEmailTokenCreated;
use Simtabi\Laranail\Auth\Dtos\CreatePendingEmailTokenInput;
use Simtabi\Laranail\Auth\Contracts\SendPendingEmailTokenInterface;

class SendPendingEmailToken implements SendPendingEmailTokenInterface
{
    public function __construct(
        private CreatePendingEmailToken $createPendingEmailToken,
    ) {
    }

    public function execute(SendPendingEmailTokenInput $input): PendingEmailToken
    {
        $pendingToken = $this->createPendingEmailToken->execute(
            input: new CreatePendingEmailTokenInput(
                email: $input->email,
            )
        );

        PendingEmailTokenCreated::dispatch(
            email: $pendingToken->email,
            token: $pendingToken->token,
        );

        return $pendingToken;
    }
}
