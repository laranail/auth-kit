<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Support\TokenResult;
use Simtabi\Laranail\Auth\Dtos\IssueTokenForUserInput;
use Simtabi\Laranail\Auth\Contracts\IssueTokenForUserInterface;

class IssueTokenForUser implements IssueTokenForUserInterface
{
    public function execute(IssueTokenForUserInput $input): TokenResult
    {
        $token = $input->user->createToken(
            name: $input->name ?? 'api-token',
            abilities: $input->abilities,
        );

        return new TokenResult(
            user: $input->user,
            token: $token->plainTextToken,
        );
    }
}
