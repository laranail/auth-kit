<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Services;

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Auth\Dto\PlannedFile;
use Simtabi\Laranail\Auth\Dto\ScaffoldPlan;

final class ScaffoldExecutor
{
    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    /**
     * @return list<PlannedFile>
     */
    public function execute(ScaffoldPlan $plan): array
    {
        $written = [];

        foreach ($plan->files as $file) {
            if ($file->exists && !$file->replace) {
                continue;
            }

            $this->files->ensureDirectoryExists(dirname($file->path));
            $this->files->put($file->path, $file->contents);

            $written[] = $file;
        }

        return $written;
    }
}
