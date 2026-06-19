<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dto;

final readonly class ScaffoldTarget
{
    public function __construct(
        public string $key,
        public string $label,
        public string $type,
        public ?string $moduleName,
        public ?string $modulePath,
        public string $basePath,
        public string $sourcePath,
        public string $modelPath,
        public string $modelNamespace,
        public string $factoryPath,
        public string $factoryNamespace,
        public string $migrationPath,
    ) {
    }
}
