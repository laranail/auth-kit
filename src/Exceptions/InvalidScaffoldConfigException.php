<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Exceptions;

use InvalidArgumentException;

final class InvalidScaffoldConfigException extends InvalidArgumentException
{
    public static function noTargets(): self
    {
        return new self('No scaffold targets are configured. Define at least one target in auth-kit.targets.');
    }

    public static function missingRequiredKey(string $targetKey, string $key): self
    {
        return new self("Scaffold target [{$targetKey}] is missing required key [{$key}].");
    }

    public static function duplicateLabel(string $label, string $keyA, string $keyB): self
    {
        return new self("Duplicate scaffold target label [{$label}] found in targets [{$keyA}] and [{$keyB}].");
    }

    public static function invalidTargetKey(string $key): self
    {
        return new self("Scaffold target [{$key}] not found in configuration.");
    }

    public static function moduleTargetMissingModulesRoot(string $key): self
    {
        return new self("Module scaffold target [{$key}] is missing required key [modules_root].");
    }

    public static function invalidPattern(string $key, string $patternKey): self
    {
        return new self("Scaffold target [{$key}] has an invalid or empty pattern for [{$patternKey}].");
    }
}
