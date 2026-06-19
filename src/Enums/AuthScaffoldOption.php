<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Enums;

enum AuthScaffoldOption: string
{
    case USE_EXISTING_MODEL = 'Use existing model';
    case CREATE_NEW_MODEL = 'Create a new one';

    public static function description(): string
    {
        return 'How would you like to scaffold?';
    }

    /**
     * @return array<int, string>
     */
    public static function labels(): array
    {
        return array_map(
            callback: fn (self $option): string => $option->value,
            array: self::cases(),
        );
    }
}
