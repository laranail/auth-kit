<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Auth Guard
    |--------------------------------------------------------------------------
    |
    | The default auth guard to use for authentication.
    |
    */
    'guard' => env('AUTH_KIT_GUARD', 'laranail'),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class to use for authentication.
    |
    */
    'model' => env('AUTH_KIT_MODEL', App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Redirect After Social Login
    |--------------------------------------------------------------------------
    |
    | The URL to redirect to after successful social login.
    |
    */
    'redirect_after_social_login' => '/',

    /*
    |--------------------------------------------------------------------------
    | Social Providers
    |--------------------------------------------------------------------------
    |
    | Configure your social login providers here. Each provider requires
    | a client_id, client_secret, and redirect URL.
    |
    */
    'social' => [
        'providers' => [
            'google' => [
                'client_id'     => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect'      => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
            ],
            'github' => [
                'client_id'     => env('GITHUB_CLIENT_ID'),
                'client_secret' => env('GITHUB_CLIENT_SECRET'),
                'redirect'      => env('GITHUB_REDIRECT_URI', env('APP_URL').'/auth/github/callback'),
            ],
            'facebook' => [
                'client_id'     => env('FACEBOOK_CLIENT_ID'),
                'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
                'redirect'      => env('FACEBOOK_REDIRECT_URI', env('APP_URL').'/auth/facebook/callback'),
            ],
            'x' => [
                'client_id'     => env('X_CLIENT_ID'),
                'client_secret' => env('X_CLIENT_SECRET'),
                'redirect'      => env('X_REDIRECT_URI', env('APP_URL').'/auth/x/callback'),
            ],
            'linkedin' => [
                'client_id'     => env('LINKEDIN_CLIENT_ID'),
                'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
                'redirect'      => env('LINKEDIN_REDIRECT_URI', env('APP_URL').'/auth/linkedin/callback'),
            ],
        ],
    ],

];
