<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Services;

use Illuminate\Filesystem\Filesystem;

final class DiscoverModules
{
    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    /**
     * @return list<array{name: string, path: string}>
     */
    public function all(string $modulesRoot): array
    {
        $resolvedRoot = base_path($modulesRoot);

        if (! $this->files->isDirectory($resolvedRoot)) {
            return [];
        }

        $modules = [];

        foreach ($this->files->directories($resolvedRoot) as $directory) {
            $name = basename($directory);
            $relativePath = $modulesRoot . '/' . $name;

            $modules[] = [
                'name' => $name,
                'path' => $relativePath,
            ];
        }

        usort($modules, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $modules;
    }
}
