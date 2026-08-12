<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Auth\Models\Social;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Simtabi\Laranail\Auth\Support\UserModelResolver;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Simtabi\Laranail\Auth\Contracts\ResolveSocialIdentityInterface;
use Simtabi\Laranail\Auth\Contracts\CreateSocialAccountActionInterface;

class ResolveSocialIdentity implements ResolveSocialIdentityInterface
{
    public function __construct(
        private CreateSocialAccountActionInterface $createSocialAccount,
    ) {
    }

    public function execute(SocialProvider $provider, SocialiteUser $socialUser, string $guard): ?Authenticatable
    {
        $social = Social::query()
            ->where('provider', $provider->value)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($social !== null) {
            $social->update([
                'token'         => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'expires_at'    => $socialUser->expiresIn
                    ? now()->addSeconds($socialUser->expiresIn)
                    : null,
            ]);

            return $social->socialable;
        }

        $userModel = UserModelResolver::resolve(guard: $guard);

        if (auth()->check()) {
            $this->createSocialAccount->execute(
                authenticatable: auth()->user(),
                provider: $provider,
                socialUser: $socialUser,
            );

            return auth()->user();
        }

        if ($this->emailIsVerified($socialUser) && ($existingUser = $this->findUserByEmail($userModel, $socialUser->getEmail())) !== null) {
            $this->createSocialAccount->execute(
                authenticatable: $existingUser,
                provider: $provider,
                socialUser: $socialUser,
            );

            return $existingUser;
        }

        if ($socialUser->getEmail() === null) {
            return null;
        }

        if ($this->findUserByEmail($userModel, $socialUser->getEmail()) !== null) {
            return null;
        }

        $user = $this->createUser($userModel, $socialUser);

        $this->createSocialAccount->execute(
            authenticatable: $user,
            provider: $provider,
            socialUser: $socialUser,
        );

        return $user;
    }

    private function emailIsVerified(SocialiteUser $socialUser): bool
    {
        $rawUser = $socialUser instanceof \Laravel\Socialite\AbstractUser
            ? $socialUser->getRaw()
            : [];

        if (! is_array($rawUser)) {
            return false;
        }

        return (bool) ($rawUser['email_verified'] ?? $rawUser['verified_email'] ?? $rawUser['verified'] ?? false);
    }

    private function findUserByEmail(string $userModel, ?string $email): ?Authenticatable
    {
        if ($email === null) {
            return null;
        }

        return $userModel::query()->where('email', $email)->first();
    }

    private function createUser(string $userModel, SocialiteUser $socialUser): Authenticatable
    {
        /** @var Model&Authenticatable $user */
        $user = new $userModel();
        $user->fill([
            'name'     => $socialUser->getName() ?? $socialUser->getNickname() ?? '',
            'email'    => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(32)),
        ]);
        $user->save();

        return $user;
    }
}
