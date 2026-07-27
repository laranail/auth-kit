<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Enums;

enum SocialProvider: string
{
    case GOOGLE = 'google';
    case FACEBOOK = 'facebook';
    case TWITTER = 'twitter';
    case LINKEDIN = 'linkedin';
    case PAYPAL = 'paypal';
}
