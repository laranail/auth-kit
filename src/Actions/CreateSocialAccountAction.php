<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Simtabi\Laranail\Auth\Models\Social;
use Simtabi\Laranail\Auth\Dtos\CreateSocialAccountActionInput;
use Simtabi\Laranail\Auth\Contracts\CreateSocialAccountActionInterface;

class CreateSocialAccountAction implements CreateSocialAccountActionInterface
{
    public function execute(CreateSocialAccountActionInput $input): Social
    {
        return Social::create([
            'socialable_type' => get_class($input->authenticatable),
            'socialable_id'   => $input->authenticatable->getAuthIdentifier(),
            'provider'        => $input->provider,
            'provider_id'     => $input->socialUser->getId(),
            'name'            => $input->socialUser->getName(),
            'nickname'        => $input->socialUser->getNickname(),
            'email'           => $input->socialUser->getEmail(),
            'avatar_path'     => $input->socialUser->getAvatar(),
            'token'           => $input->socialUser->token,
            'refresh_token'   => $input->socialUser->refreshToken,
            'expires_at'      => $input->socialUser->expiresIn
                ? now()->addSeconds($input->socialUser->expiresIn)
                : null,
        ]);
    }
}
