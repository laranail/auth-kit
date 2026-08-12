<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

use Simtabi\Laranail\Auth\Support\TokenResult;
use Simtabi\Laranail\Auth\Dtos\IssueTokenForUserInput;

interface IssueTokenForUserInterface
{
    public function execute(IssueTokenForUserInput $input): TokenResult;
}
