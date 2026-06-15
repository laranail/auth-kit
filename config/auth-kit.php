<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Models
    |--------------------------------------------------------------------------
    |
    | Define each authenticatable model and its auth configuration.
    | Each entry creates its own set of routes, controllers, and migrations.
    |
    */
    'models' => [],

    /*
    |--------------------------------------------------------------------------
    | Auth Providers
    |--------------------------------------------------------------------------
    |
    | Provider configuration for auth.php.
    | The install command will add entries here for each model.
    |
    */
    'providers' => [],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication (Optional)
    |--------------------------------------------------------------------------
    |
    | Provide a class that implements Simtabi\Laranail\Auth\Contracts\TwoFactorProvider
    | to enable 2FA support. Set to null to disable.
    |
    */
    'two_factor' => [
        'enabled'  => false,
        'provider' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Default middleware and prefix for auth routes.
    |
    */
    'routes' => [
        'middleware' => ['web'],
        'prefix'     => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Keys
    |--------------------------------------------------------------------------
    |
    | Keys used to store pending auth state in the session.
    |
    */
    'session' => [
        'pending_token'   => 'auth_kit_pending_token',
        'pending_user_id' => 'auth_kit_pending_user_id',
    ],
];
