<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Dto;

use Simtabi\Laranail\Auth\Enums\Stack;

final readonly class ScaffoldPlan
{
    /**
     * @param  array<int, PlannedFile>  $files
     */
    public function __construct(
        public ScaffoldTarget $target,
        public string $modelClass,
        public Stack $stack,
        public array $files,
    ) {
    }
}
