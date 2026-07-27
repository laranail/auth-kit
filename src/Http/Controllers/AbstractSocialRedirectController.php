<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Simtabi\Laranail\Auth\Actions\SocialRedirectAction;
use Simtabi\Laranail\Auth\Dtos\SocialRedirectActionInput;

abstract class AbstractSocialRedirectController extends AbstractAuthController
{
    public function __invoke(Request $request, SocialRedirectAction $action): mixed
    {
        $provider = SocialProvider::from(value: $request->route('provider'));

        $result = $action->execute(
            input: new SocialRedirectActionInput(
                provider: $provider,
            )
        );

        return $this->redirect(request: $request, url: $result->url);
    }

    abstract protected function redirect(Request $request, string $url): mixed;
}
