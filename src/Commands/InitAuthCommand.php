<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Simtabi\Laranail\Auth\Enums\AuthScaffoldOption;
use Simtabi\Laranail\Auth\Actions\GetAvailableModels;

#[AsCommand(
    name: 'auth-kit:init',
    description: 'Scaffold a new authentication for your application. '
)]
class InitAuthCommand extends Command
{
    public function __construct(
        private readonly Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(GetAvailableModels $getAvailableModels): int
    {
        info(message: 'This command help you scaffold an authentication.');

        $method = AuthScaffoldOption::from(select(
            label: AuthScaffoldOption::description(),
            options: AuthScaffoldOption::labels(),
        ));

        if ($method === AuthScaffoldOption::USE_EXISTING_MODEL) {
            $models = $getAvailableModels();

            if (empty($models)) {
                error(message: 'No models found in Models or models directory.');

                return self::FAILURE;
            }

            select(
                label: 'Which model would you like to scaffold authentication for?',
                options: $models,
            );
        }

        if ($method === AuthScaffoldOption::CREATE_NEW_MODEL) {
            $path = text(
                label: 'Enter the model path (e.g., app/Models/User)',
                required: true,
            );

            $segments = explode(separator: '/', string: $path);
            $className = (string) array_pop($segments);
            $namespace = implode(separator: '\\', array: array_map(callback: 'ucfirst', array: $segments));

            $namespace = text(
                label: 'Confirm the model namespace',
                default: $namespace,
            );

            $modelFile = base_path($path . '.php');
            $modelAlreadyExists = $this->files->exists($modelFile);

            $modelStub = $this->resolveStub(name: 'model.php.stub');

            $modelContent = str_replace(
                search: ['{{ namespace }}', '{{ class }}', '{{ factory_namespace }}'],
                replace: [$namespace, $className, $this->getFactoryNamespace($namespace)],
                subject: $modelStub,
            );

            $this->files->ensureDirectoryExists(dirname($modelFile));
            $this->files->put($modelFile, $modelContent);

            info(
                message: $modelAlreadyExists
                    ? "Model updated at {$path}.php"
                    : "Model created at {$path}.php"
            );

            $factoryPath = $this->resolveFactoryPath(namespace: $namespace, className: $className);

            if ($factoryPath !== null) {
                $factoryFile = base_path($factoryPath);

                $factoryStub = $this->resolveStub(name: 'factory.php.stub');

                $factoryContent = str_replace(
                    search: ['{{ namespace }}', '{{ model_namespace }}', '{{ model_class }}', '{{ class }}'],
                    replace: [$this->getFactoryNamespace($namespace), $namespace, $className, $className . 'Factory'],
                    subject: $factoryStub,
                );

                $this->files->ensureDirectoryExists(dirname($factoryFile));
                $this->files->put($factoryFile, $factoryContent);

                info(message: "Factory created at {$factoryPath}");
            }
        }

        return self::SUCCESS;
    }

    private function resolveStub(string $name): string
    {
        $published = base_path('auth-kit-stubs/' . $name);

        if ($this->files->exists($published)) {
            return $this->files->get($published);
        }

        return $this->files->get(__DIR__ . '/../../stubs/' . $name);
    }

    private function resolveFactoryPath(string $namespace, string $className): ?string
    {
        $composerJson = base_path('composer.json');

        if (! $this->files->exists($composerJson)) {
            return null;
        }

        $composer = json_decode(
            $this->files->get($composerJson),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $psr4 = $composer['autoload']['psr-4'] ?? [];

        $factoryNamespaces = array_filter(
            $psr4,
            fn (string $path, string $ns): bool => str_contains($ns, 'Factories') || str_contains($path, 'factories'),
            ARRAY_FILTER_USE_BOTH,
        );

        if (empty($factoryNamespaces)) {
            return null;
        }

        $factoryNs = array_key_first($factoryNamespaces);
        $factoryDir = mb_rtrim($factoryNamespaces[$factoryNs], '/');

        $factoryClass = $className . 'Factory';
        $factoryFile = $factoryDir . '/' . $factoryClass . '.php';

        return $factoryFile;
    }

    private function getFactoryNamespace(string $modelNamespace): string
    {
        $composerJson = base_path('composer.json');

        if (! $this->files->exists($composerJson)) {
            return 'Database\\Factories';
        }

        $composer = json_decode(
            $this->files->get($composerJson),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $psr4 = $composer['autoload']['psr-4'] ?? [];

        foreach ($psr4 as $namespace => $path) {
            if (str_contains($namespace, 'Factories') || str_contains($path, 'factories')) {
                return mb_rtrim($namespace, '\\');
            }
        }

        return 'Database\\Factories';
    }
}
