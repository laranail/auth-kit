<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dto;

final readonly class PlannedFile
{
    public function __construct(
        public string $path,
        public string $contents,
        public string $description,
        public bool $exists,
        public bool $replace,
    ) {
    }
}
