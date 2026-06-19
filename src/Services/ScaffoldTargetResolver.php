<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Simtabi\Laranail\Auth\Dto\ScaffoldTarget;

final class ScaffoldTargetResolver
{
    public function __construct(
        private readonly ScaffoldTargetRepository $repository,
        private readonly DiscoverModules $discoverModules,
    ) {
    }

    public function resolve(string $key, ?string $moduleName = null): ScaffoldTarget
    {
        $config = $this->repository->find($key);

        if ($config['type'] === 'module') {
            return $this->resolveModuleTarget($key, $config, $moduleName);
        }

        return $this->resolveRootTarget($key, $config);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function resolveRootTarget(string $key, array $config): ScaffoldTarget
    {
        return new ScaffoldTarget(
            key: $key,
            label: $config['label'],
            type: 'root',
            moduleName: null,
            modulePath: null,
            basePath: '',
            sourcePath: $config['source_path'],
            modelPath: $config['model_path'],
            modelNamespace: $config['model_namespace'],
            factoryPath: $config['factory_path'],
            factoryNamespace: $config['factory_namespace'],
        );
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function resolveModuleTarget(string $key, array $config, ?string $moduleName): ScaffoldTarget
    {
        if ($moduleName === null || $moduleName === '') {
            throw new InvalidArgumentException("Module name is required for scaffold target [{$key}].");
        }

        $modules = $this->discoverModules->all($config['modules_root']);
        $moduleNames = array_column($modules, 'name');

        if (! in_array($moduleName, $moduleNames, true)) {
            throw new InvalidArgumentException(
                "Module [{$moduleName}] not found in [{$config['modules_root']}/]."
            );
        }

        $modulePath = $config['modules_root'] . '/' . $moduleName;
        $studlyModule = Str::studly($moduleName);

        return new ScaffoldTarget(
            key: $key,
            label: $config['label'],
            type: 'module',
            moduleName: $studlyModule,
            modulePath: $modulePath,
            basePath: $modulePath,
            sourcePath: $modulePath . '/' . $config['source_path'],
            modelPath: str_replace(
                ['{module}', '{module_path}'],
                [$studlyModule, $modulePath],
                $config['model_path_pattern'],
            ),
            modelNamespace: str_replace(
                ['{module}', '{module_path}'],
                [$studlyModule, $modulePath],
                $config['model_namespace_pattern'],
            ),
            factoryPath: str_replace(
                ['{module}', '{module_path}'],
                [$studlyModule, $modulePath],
                $config['factory_path_pattern'],
            ),
            factoryNamespace: str_replace(
                ['{module}', '{module_path}'],
                [$studlyModule, $modulePath],
                $config['factory_namespace_pattern'],
            ),
        );
    }
}
