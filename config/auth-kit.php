<?php

declare(strict_types=1);

return [
    'guard' => env(key: 'AUTH_KIT_GUARD', default: 'web'),

    'user_model' => env(key: 'AUTH_KIT_USER_MODEL'),

    'rate_limit' => [
        'max_attempts'  => (int) env(key: 'AUTH_KIT_RATE_LIMIT_MAX_ATTEMPTS', default: 5),
        'decay_minutes' => (int) env(key: 'AUTH_KIT_RATE_LIMIT_DECAY_MINUTES', default: 1),
    ],

    'turnstile' => [
        'enabled'    => (bool) env(key: 'AUTH_KIT_TURNSTILE_ENABLED', default: false),
        'site_key'   => env(key: 'TURNSTILE_SITE_KEY'),
        'secret_key' => env(key: 'TURNSTILE_SECRET_KEY'),
        'url'        => env(key: 'TURNSTILE_URL', default: 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),
        'input'      => 'cf-turnstile-response',
    ],

    'fortify' => [
        'views' => false,

        'features' => [
            'reset-passwords',
            'update-profile-information',
            'update-passwords',
            'email-verification',
            'passkeys',
        ],
    ],

    'social' => [
        'google' => [
            'client_id'     => env(key: 'AUTH_KIT_GOOGLE_CLIENT_ID'),
            'client_secret' => env(key: 'AUTH_KIT_GOOGLE_CLIENT_SECRET'),
            'redirect'      => env(key: 'AUTH_KIT_GOOGLE_REDIRECT'),
            'scopes'        => ['openid', 'profile', 'email'],
        ],

        'facebook' => [
            'client_id'     => env(key: 'AUTH_KIT_FACEBOOK_CLIENT_ID'),
            'client_secret' => env(key: 'AUTH_KIT_FACEBOOK_CLIENT_SECRET'),
            'redirect'      => env(key: 'AUTH_KIT_FACEBOOK_REDIRECT'),
            'scopes'        => ['email', 'public_profile'],
        ],

        'twitter' => [
            'client_id'     => env(key: 'AUTH_KIT_TWITTER_CLIENT_ID'),
            'client_secret' => env(key: 'AUTH_KIT_TWITTER_CLIENT_SECRET'),
            'redirect'      => env(key: 'AUTH_KIT_TWITTER_REDIRECT'),
            'scopes'        => ['users.read', 'users.email', 'tweet.read'],
        ],

        'linkedin' => [
            'client_id'     => env(key: 'AUTH_KIT_LINKEDIN_CLIENT_ID'),
            'client_secret' => env(key: 'AUTH_KIT_LINKEDIN_CLIENT_SECRET'),
            'redirect'      => env(key: 'AUTH_KIT_LINKEDIN_REDIRECT'),
            'scopes'        => ['openid', 'profile', 'email'],
        ],

        'paypal' => [
            'client_id'     => env(key: 'AUTH_KIT_PAYPAL_CLIENT_ID'),
            'client_secret' => env(key: 'AUTH_KIT_PAYPAL_CLIENT_SECRET'),
            'redirect'      => env(key: 'AUTH_KIT_PAYPAL_REDIRECT'),
            'sandbox_mode'  => (bool) env(key: 'AUTH_KIT_PAYPAL_SANDBOX_MODE', default: true),
            'scopes'        => ['openid', 'profile', 'email'],
        ],
    ],
];
