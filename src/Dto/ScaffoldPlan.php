<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dto;

final readonly class ScaffoldPlan
{
    /**
     * @param  array<int, PlannedFile>  $files
     */
    public function __construct(
        public ScaffoldTarget $target,
        public string $modelClass,
        public array $files,
    ) {
    }
}
