<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Enums;

enum SocialProviderEnum: string
{
    case GOOGLE = 'google';
    case GITHUB = 'github';
    case FACEBOOK = 'facebook';
    case TWITTER = 'x';
    case LINKEDIN = 'linkedin';

    public function getLabel(): string
    {
        return match ($this) {
            self::GOOGLE   => 'Google',
            self::GITHUB   => 'GitHub',
            self::FACEBOOK => 'Facebook',
            self::TWITTER  => 'X (Twitter)',
            self::LINKEDIN => 'LinkedIn',
        };
    }

    public function getScopes(): array
    {
        return match ($this) {
            self::GOOGLE   => ['openid', 'profile', 'email'],
            self::GITHUB   => ['user:email'],
            self::FACEBOOK => ['email'],
            self::TWITTER  => ['users.read', 'users.email'],
            self::LINKEDIN => ['openid', 'profile', 'email'],
        };
    }
}
