<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Support;

class AuthKit
{
    public static function guard(): string
    {
        return config(key: 'auth-kit.guard', default: 'web');
    }

    public static function redirect(string $key, string $default = '/'): string
    {
        return config(key: "auth-kit.redirects.{$key}", default: $default);
    }

    public static function afterLoginRedirect(): string
    {
        return self::redirect('after_login', '/dashboard');
    }

    public static function afterRegistrationRedirect(): string
    {
        return self::redirect('after_registration', '/dashboard');
    }

    public static function afterLogoutRedirect(): string
    {
        return self::redirect('after_logout', '/');
    }

    public static function afterPasswordResetRedirect(): string
    {
        return self::redirect('after_password_reset', '/login');
    }

    public static function afterEmailVerificationRedirect(): string
    {
        return self::redirect('after_email_verification', '/dashboard?verified=1');
    }
}
