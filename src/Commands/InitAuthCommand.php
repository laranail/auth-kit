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

            $targetFile = base_path($path . '.php');

            if ($this->files->exists($targetFile)) {
                error(message: "Model already exists at {$path}.php");

                return self::FAILURE;
            }

            $stub = $this->resolveStub(name: 'model.php.stub');

            $content = str_replace(
                search: ['{{ namespace }}', '{{ class }}'],
                replace: [$namespace, $className],
                subject: $stub,
            );

            $this->files->ensureDirectoryExists(dirname($targetFile));
            $this->files->put($targetFile, $content);

            info(message: "Model created at {$path}.php");
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
}
