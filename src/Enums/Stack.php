<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Enums;

enum Stack: string
{
    case Blade = 'blade';

    /**
     * Get all available stacks with human-readable labels.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Blade->value => 'Blade',
        ];
    }
}
