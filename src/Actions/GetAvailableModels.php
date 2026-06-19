<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Actions;

use SplFileInfo;
use Illuminate\Support\Str;
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
    public function __invoke(): array
    {
        $models = [];

        foreach ($this->resolveModelPaths() as $path) {
            if (! $this->files->isDirectory(directory: $path)) {
                continue;
            }

            foreach ($this->files->allFiles(directory: $path) as $file) {
                $class = $this->extractClassName(file: $file);

                if ($class !== null) {
                    $models[] = $class;
                }
            }
        }

        sort(array: $models);

        return array_values(array: array_unique(array: $models));
    }

    /**
     * @return list<string>
     */
    private function resolveModelPaths(): array
    {
        $paths = [
            'app/{Models,models}',
            'app-modules/*/src/{Models,models}',
            'modules/*/{Models,models}',
        ];
        $resolvedPaths = [];

        foreach ($paths as $path) {
            foreach ($this->expandPathPattern(pattern: $path) as $expandedPath) {
                $resolvedPaths[] = base_path(path: $expandedPath);
            }
        }

        return array_values(array: array_unique(array: $resolvedPaths));
    }

    /**
     * @return list<string>
     */
    private function expandPathPattern(string $pattern): array
    {
        $paths = [$pattern];

        if (Str::contains(haystack: $pattern, needles: '{') && Str::contains(haystack: $pattern, needles: '}')) {
            preg_match(pattern: '/\{([^}]+)}/', subject: $pattern, matches: $matches);

            if ($matches !== []) {
                $paths = [];

                foreach (explode(separator: ',', string: $matches[1]) as $segment) {
                    $paths[] = preg_replace(pattern: '/\{[^}]+}/', replacement: mb_trim(string: $segment), subject: $pattern, limit: 1);
                }
            }
        }

        $expandedPaths = [];

        foreach ($paths as $path) {
            if (! Str::contains(haystack: $path, needles: '*')) {
                $expandedPaths[] = $path;

                continue;
            }

            $matches = glob(pattern: base_path(path: $path), flags: GLOB_ONLYDIR);

            if ($matches === false) {
                continue;
            }

            foreach ($matches as $match) {
                $expandedPaths[] = str_replace(search: base_path() . DIRECTORY_SEPARATOR, replace: '', subject: $match);
            }
        }

        return array_values(array: array_unique(array: $expandedPaths));
    }

    private function extractClassName(SplFileInfo $file): ?string
    {
        if ($file->getExtension() !== 'php') {
            return null;
        }

        $tokens = token_get_all(code: $this->files->get(path: $file->getPathname()));
        $namespace = '';

        foreach ($tokens as $index => $token) {
            if (! is_array(value: $token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                $namespace = $this->readNamespace(tokens: $tokens, namespaceIndex: $index);
            }

            if ($token[0] === T_CLASS) {
                $class = $this->readClass(tokens: $tokens, classIndex: $index);

                return $class === null ? null : mb_ltrim(string: $namespace . '\\' . $class, characters: '\\');
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

        for ($index = $namespaceIndex + 1; $index < count(value: $tokens); $index++) {
            $token = $tokens[$index];

            if ($token === ';' || $token === '{') {
                break;
            }

            if (is_array(value: $token) && in_array(needle: $token[0], haystack: [T_NAME_QUALIFIED, T_STRING, T_NS_SEPARATOR], strict: true)) {
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
        for ($index = $classIndex + 1; $index < count(value: $tokens); $index++) {
            $token = $tokens[$index];

            if (is_array(value: $token) && $token[0] === T_STRING) {
                return $token[1];
            }
        }

        return null;
    }
}
