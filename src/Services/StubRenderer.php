<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Services;

use Illuminate\Filesystem\Filesystem;

final class StubRenderer
{
    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    public function render(string $stubName, array $variables): string
    {
        $stub = $this->resolveStub($stubName);

        return str_replace(
            array_keys($variables),
            array_values($variables),
            $stub,
        );
    }

    private function resolveStub(string $name): string
    {
        $published = base_path('auth-kit-stubs/' . $name);

        if ($this->files->exists($published)) {
            return $this->files->get($published);
        }

        return $this->files->get(__DIR__ . '/../../stubs/' . $name);
    }
}
