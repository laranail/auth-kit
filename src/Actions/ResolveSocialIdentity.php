<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use LogicException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Auth\Models\Social;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Auth\Dtos\ResolveSocialIdentityInput;
use Simtabi\Laranail\Auth\Dtos\CreateSocialAccountActionInput;
use Simtabi\Laranail\Auth\Contracts\ResolveSocialIdentityInterface;
use Simtabi\Laranail\Auth\Contracts\CreateSocialAccountActionInterface;

class ResolveSocialIdentity implements ResolveSocialIdentityInterface
{
    public function __construct(
        private CreateSocialAccountActionInterface $createSocialAccount,
    ) {
    }

    public function execute(ResolveSocialIdentityInput $input): ?Authenticatable
    {
        $social = Social::query()
            ->where('provider', $input->provider->value)
            ->where('provider_id', $input->socialUser->getId())
            ->first();

        if ($social !== null) {
            $social->update([
                'token'         => $input->socialUser->token,
                'refresh_token' => $input->socialUser->refreshToken,
                'expires_at'    => $input->socialUser->expiresIn
                    ? now()->addSeconds($input->socialUser->expiresIn)
                    : null,
            ]);

            return $social->socialable;
        }

        $userModel = $this->userModel($input->guard);

        if (auth()->check()) {
            $this->createSocialAccount->execute(new CreateSocialAccountActionInput(
                authenticatable: auth()->user(),
                provider: $input->provider,
                socialUser: $input->socialUser,
            ));

            return auth()->user();
        }

        if ($this->emailIsVerified($input) && ($existingUser = $this->findUserByEmail($userModel, $input->socialUser->getEmail())) !== null) {
            $this->createSocialAccount->execute(new CreateSocialAccountActionInput(
                authenticatable: $existingUser,
                provider: $input->provider,
                socialUser: $input->socialUser,
            ));

            return $existingUser;
        }

        if ($input->socialUser->getEmail() === null) {
            return null;
        }

        if ($this->findUserByEmail($userModel, $input->socialUser->getEmail()) !== null) {
            return null;
        }

        $user = $this->createUser($userModel, $input);

        $this->createSocialAccount->execute(new CreateSocialAccountActionInput(
            authenticatable: $user,
            provider: $input->provider,
            socialUser: $input->socialUser,
        ));

        return $user;
    }

    private function emailIsVerified(ResolveSocialIdentityInput $input): bool
    {
        $rawUser = $input->socialUser instanceof \Laravel\Socialite\AbstractUser
            ? $input->socialUser->getRaw()
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

    private function createUser(string $userModel, ResolveSocialIdentityInput $input): Authenticatable
    {
        /** @var Model&Authenticatable $user */
        $user = new $userModel();
        $user->fill([
            'name'     => $input->socialUser->getName() ?? $input->socialUser->getNickname() ?? '',
            'email'    => $input->socialUser->getEmail(),
            'password' => Hash::make(Str::random(32)),
        ]);
        $user->save();

        return $user;
    }

    private function userModel(string $guard): string
    {
        $model = config('auth-kit.user_model');

        if (! is_string($model) || $model === '') {
            $provider = config("auth.guards.{$guard}.provider", config('auth.defaults.provider'));
            $model = config("auth.providers.{$provider}.model");
        }

        if (! is_string($model) || ! is_a($model, Model::class, allow_string: true) || ! is_a($model, Authenticatable::class, allow_string: true)) {
            throw new LogicException('The configured auth-kit user model must be an Eloquent Authenticatable model.');
        }

        return $model;
    }
}
