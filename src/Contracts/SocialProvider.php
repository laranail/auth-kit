<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Contracts;

interface SocialProvider
{
    public function getName(): string;

    public function getLabel(): string;

    public function getIcon(): string;

    public function getColorClasses(): string;

    public function getSocialiteDriver(): string;

    public function getRedirectUrl(): string;

    public function getCallbackUrl(): string;

    public function isEnabled(): bool;
}
