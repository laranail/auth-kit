<?php

declare(strict_types=1);

return [
    'guard' => env(key: 'AUTH_KIT_GUARD', default: 'web'),

    'rate_limit' => [
        'max_attempts'  => (int) env(key: 'AUTH_KIT_RATE_LIMIT_MAX_ATTEMPTS', default: 5),
        'decay_minutes' => (int) env(key: 'AUTH_KIT_RATE_LIMIT_DECAY_MINUTES', default: 1),
    ],
];
