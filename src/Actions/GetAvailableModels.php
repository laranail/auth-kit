<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use SplFileInfo;
use Illuminate\Filesystem\Filesystem;

class GetAvailableModels
{
    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    /**
     * @return list<string>
     */
    public function __invoke(?string $basePath = null): array
    {
        $basePath ??= app_path();

        $models = [];

        foreach (['Models', 'models'] as $directory) {
            $path = $basePath . DIRECTORY_SEPARATOR . $directory;

            if (! $this->files->isDirectory($path)) {
                continue;
            }

            foreach ($this->files->allFiles($path) as $file) {
                $class = $this->extractClassName($file);

                if ($class !== null) {
                    $models[] = $class;
                }
            }
        }

        sort($models);

        return array_values(array_unique($models));
    }

    private function extractClassName(SplFileInfo $file): ?string
    {
        if ($file->getExtension() !== 'php') {
            return null;
        }

        $tokens = token_get_all($this->files->get($file->getPathname()));
        $namespace = '';

        foreach ($tokens as $index => $token) {
            if (! is_array($token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                $namespace = $this->readNamespace($tokens, $index);
            }

            if ($token[0] === T_CLASS) {
                $class = $this->readClass($tokens, $index);

                return $class === null ? null : mb_ltrim($namespace . '\\' . $class, '\\');
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $tokens
     */
    private function readNamespace(array $tokens, int $namespaceIndex): string
    {
        $namespace = '';

        for ($index = $namespaceIndex + 1; $index < count($tokens); $index++) {
            $token = $tokens[$index];

            if ($token === ';' || $token === '{') {
                break;
            }

            if (is_array($token) && in_array($token[0], [T_NAME_QUALIFIED, T_STRING, T_NS_SEPARATOR], true)) {
                $namespace .= $token[1];
            }
        }

        return $namespace;
    }

    /**
     * @param  array<int, mixed>  $tokens
     */
    private function readClass(array $tokens, int $classIndex): ?string
    {
        for ($index = $classIndex + 1; $index < count($tokens); $index++) {
            $token = $tokens[$index];

            if (is_array($token) && $token[0] === T_STRING) {
                return $token[1];
            }
        }

        return null;
    }
}
