<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\table;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'auth-kit:install', description: 'Scaffold authentication for an authenticatable model')]
class InstallCommand extends Command
{
    public function __construct(
        protected Filesystem $files,
        protected Migrator $migrator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        info(message: 'Auth Kit - Authentication Scaffolding');

        // Step 1: Select the authenticatable model
        $modelClass = $this->askModel();
        if (! $modelClass) {
            return self::FAILURE;
        }

        $modelShortName = class_basename(class: $modelClass);
        $modelKey = Str::snake(value: $modelShortName);
        $tableName = $this->resolveTableName(modelClass: $modelClass);

        // Step 2: Check if migration exists
        $existingMigration = $this->findExistingMigration(tableName: $tableName);
        $amendMigration = false;

        if ($existingMigration) {
            $amendMigration = confirm(
                label: "Found existing migration: {$existingMigration}. Would you like to amend it?",
                default: true
            );
        }

        // Step 3: Show summary
        $routePrefix = Str::lower(value: $modelShortName);
        $redirectAfterLogin = '/';
        table(
            headers: ['Setting', 'Value'],
            rows: [
                ['Model', $modelClass],
                ['Table', $tableName],
                ['Route Prefix', $routePrefix],
                ['Redirect After Login', $redirectAfterLogin],
                ['Migration', $amendMigration ? 'Amend existing' : ($existingMigration ? 'Skip (already exists)' : 'Create new')],
            ]
        );

        if (! confirm(label: 'Proceed with installation?', default: true)) {
            info(message: 'Installation cancelled.');
            return self::SUCCESS;
        }

        // Step 4: Publish files
        $config = [
            'model_class'          => $modelClass,
            'model_short_name'     => $modelShortName,
            'model_key'            => $modelKey,
            'table_name'           => $tableName,
            'route_prefix'         => $routePrefix,
            'redirect_after_login' => $redirectAfterLogin,
        ];

        $this->publishConfig(config: $config);
        $this->publishRoutes(config: $config);
        $this->publishMigration(config: $config, amend: $amendMigration);
        $this->publishControllers(config: $config);
        $this->publishRequests(config: $config);
        $this->publishActions(config: $config);
        $this->publishModelConcern(config: $config);
        $this->publishTests(config: $config);

        // Step 5: Show next steps
        info(message: 'Installation complete!');
        $this->newLine();
        $this->line(string: 'Next steps:');
        $this->line(string: '  1. Run: php artisan migrate');
        $this->line(string: "  2. Add the Has{$modelShortName}Auth trait to your {$modelShortName} model");
        $this->line(string: "  3. Add the provider to config/auth.php providers array:");
        $this->line(string: "     '{$modelKey}' => ['driver' => 'eloquent', 'model' => {$modelClass}::class]");
        $this->line(string: "  4. Set the guard's provider in config/auth.php guards array");
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Ask the user to select the authenticatable model.
     */
    private function askModel(): ?string
    {
        $models = $this->discoverModels();

        if (empty($models)) {
            return text(
                label: 'No Eloquent models found. Enter the fully qualified model class name',
                default: 'App\\Models\\User',
                required: true
            );
        }

        $options = array_merge($models, ['Enter manually']);

        $choice = select(
            label: 'Which authenticatable model are you configuring?',
            options: $options,
            hint: 'Select a model or enter manually.'
        );

        if ($choice === 'Enter manually') {
            return text(
                label: 'Enter the fully qualified model class name',
                default: 'App\\Models\\User',
                required: true
            );
        }

        return $choice;
    }

    /**
     * Discover Eloquent models in the application.
     *
     * @return list<string>
     */
    private function discoverModels(): array
    {
        $paths = [
            app_path(path: 'Models'),
            app_path(path: ''),
        ];

        $models = [];

        foreach ($paths as $path) {
            if (! is_dir(filename: $path)) {
                continue;
            }

            $files = glob(pattern: $path . '/*.php');

            foreach ($files as $file) {
                $content = file_get_contents(filename: $file);

                if (str_contains(haystack: $content, needle: 'extends Model') || str_contains(haystack: $content, needle: 'extends Authenticatable')) {
                    $className = Str::before(subject: basename(path: $file), search: '.php');
                    $namespace = $this->extractNamespace(content: $content);

                    if ($namespace) {
                        $models[] = $namespace . '\\' . $className;
                    } else {
                        $models[] = 'App\\Models\\' . $className;
                    }
                }
            }
        }

        return array_unique(array: $models);
    }

    /**
     * Extract the namespace from a PHP file content.
     */
    private function extractNamespace(string $content): ?string
    {
        if (preg_match(pattern: '/namespace\s+([^;]+);/', subject: $content, matches: $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Resolve the table name for the given model.
     */
    private function resolveTableName(string $modelClass): string
    {
        if (method_exists(object_or_class: $modelClass, method: 'getTable')) {
            $instance = new $modelClass();
            return $instance->getTable();
        }

        return Str::plural(value: Str::snake(value: class_basename(class: $modelClass)));
    }

    /**
     * Find an existing migration file for the given table.
     */
    private function findExistingMigration(string $tableName): ?string
    {
        $migrationPath = database_path(path: 'migrations');

        if (! is_dir(filename: $migrationPath)) {
            return null;
        }

        $files = glob(pattern: $migrationPath . '/*_create_' . $tableName . '_table.php');

        return $files ? basename(path: $files[0]) : null;
    }

    /**
     * Publish the config file.
     *
     * @param  array<string, mixed>  $config
     */
    private function publishConfig(array $config): void
    {
        $stub = $this->files->get(path: __DIR__ . '/../../stubs/config.php.stub');

        $stub = str_replace(
            search: ['{{MODEL_CLASS}}', '{{MODEL_KEY}}', '{{ROUTE_PREFIX}}'],
            replace: [$config['model_class'], $config['model_key'], $config['route_prefix']],
            subject: $stub
        );

        $target = config_path(path: 'auth-kit.php');

        if (file_exists(filename: $target)) {
            $existing = include $target;
            $existing['models'][$config['model_key']] = [
                'model'  => $config['model_class'],
                'routes' => [
                    'prefix'     => $config['route_prefix'],
                    'middleware' => ['web'],
                ],
                'redirect' => $config['redirect_after_login'],
            ];
            $existing['providers'][$config['model_key']] = [
                'driver' => 'eloquent',
                'model'  => $config['model_class'],
            ];

            $content = var_export(value: $existing, return: true);
            $this->files->put(path: $target, contents: "<?php\n\ndeclare(strict_types=1);\n\nreturn {$content};\n");
        } else {
            $this->files->put(path: $target, contents: $stub);
        }

        $this->line(string: "  ✓ config/auth-kit.php");
    }

    /**
     * Publish the routes file.
     *
     * @param  array<string, mixed>  $config
     */
    private function publishRoutes(array $config): void
    {
        $stub = $this->files->get(path: __DIR__ . '/../../stubs/routes.php.stub');

        $stub = str_replace(
            search: ['{{MODEL_SHORT_NAME}}', '{{MODEL_KEY}}', '{{ROUTE_PREFIX}}', '{{CONTROLLER_NAMESPACE}}'],
            replace: [$config['model_short_name'], $config['model_key'], $config['route_prefix'], "App\\Http\\Controllers\\Auth\\{$config['model_short_name']}"],
            subject: $stub
        );

        $target = base_path(path: "routes/auth-{$config['model_key']}.php");
        $this->files->put(path: $target, contents: $stub);

        $this->line(string: "  ✓ routes/auth-{$config['model_key']}.php");
    }

    /**
     * Publish or amend the migration.
     *
     * @param  array<string, mixed>  $config
     */
    private function publishMigration(array $config, bool $amend): void
    {
        if ($amend) {
            $stub = $this->files->get(path: __DIR__ . '/../../stubs/migration-amend.php.stub');
        } else {
            $stub = $this->files->get(path: __DIR__ . '/../../stubs/migration-create.php.stub');
        }

        $stub = str_replace(
            search: ['{{TABLE_NAME}}', '{{MODEL_SHORT_NAME}}'],
            replace: [$config['table_name'], $config['model_short_name']],
            subject: $stub
        );

        $timestamp = date(format: 'Y_m_d_His');
        $target = database_path(path: "migrations/{$timestamp}_auth_kit_{$config['model_key']}.php");
        $this->files->put(path: $target, contents: $stub);

        $this->line(string: "  ✓ database/migrations/{$timestamp}_auth_kit_{$config['model_key']}.php");
    }

    /**
     * Publish the login controller.
     *
     * @param  array<string, mixed>  $config
     */
    private function publishControllers(array $config): void
    {
        $stub = $this->files->get(path: __DIR__ . '/../../stubs/login-controller.php.stub');

        $stub = str_replace(
            search: ['{{MODEL_SHORT_NAME}}', '{{MODEL_KEY}}', '{{NAMESPACE}}', '{{REDIRECT_AFTER_LOGIN}}'],
            replace: [$config['model_short_name'], $config['model_key'], "App\\Http\\Controllers\\Auth\\{$config['model_short_name']}", $config['redirect_after_login']],
            subject: $stub
        );

        $controllerDir = app_path(path: "Http/Controllers/Auth/{$config['model_short_name']}");
        $this->files->makeDirectory(path: $controllerDir, recursive: true, force: true);

        $target = "{$controllerDir}/LoginController.php";
        $this->files->put(path: $target, contents: $stub);

        $this->line(string: "  ✓ app/Http/Controllers/Auth/{$config['model_short_name']}/LoginController.php");

        // Register controller route in the published routes file
        $this->registerControllerRoutes(config: $config);
    }

    /**
     * Register controller routes in the auth routes file.
     *
     * @param  array<string, mixed>  $config
     */
    private function registerControllerRoutes(array $config): void
    {
        $routeFile = base_path(path: "routes/auth-{$config['model_key']}.php");

        if (! file_exists(filename: $routeFile)) {
            return;
        }

        $content = file_get_contents(filename: $routeFile);
        $routePrefix = $config['route_prefix'];

        $content = str_replace(
            search: '{{CONTROLLER_ROUTES}}',
            replace: "\n// Login\nRoute::get('/{$routePrefix}/login', [{$config['model_short_name']}LoginController::class, 'create'])->name('{$config['model_key']}.login.create');\nRoute::post('/{$routePrefix}/login', [{$config['model_short_name']}LoginController::class, 'store'])->name('{$config['model_key']}.login.store');\nRoute::post('/{$routePrefix}/logout', [{$config['model_short_name']}LoginController::class, 'destroy'])->name('{$config['model_key']}.logout');",
            subject: $content
        );

        file_put_contents(filename: $routeFile, data: $content);
    }

    /**
     * Publish the login request.
     *
     * @param  array<string, mixed>  $config
     */
    private function publishRequests(array $config): void
    {
        $stub = $this->files->get(path: __DIR__ . '/../../stubs/login-request.php.stub');

        $stub = str_replace(
            search: ['{{MODEL_SHORT_NAME}}', '{{NAMESPACE}}'],
            replace: [$config['model_short_name'], "App\\Http\\Requests\\Auth\\{$config['model_short_name']}"],
            subject: $stub
        );

        $requestDir = app_path(path: "Http/Requests/Auth/{$config['model_short_name']}");
        $this->files->makeDirectory(path: $requestDir, recursive: true, force: true);

        $target = "{$requestDir}/LoginRequest.php";
        $this->files->put(path: $target, contents: $stub);

        $this->line(string: "  ✓ app/Http/Requests/Auth/{$config['model_short_name']}/LoginRequest.php");
    }

    /**
     * Publish the auth actions.
     *
     * @param  array<string, mixed>  $config
     */
    private function publishActions(array $config): void
    {
        $actionsDir = app_path(path: "Actions/Auth/{$config['model_short_name']}");
        $this->files->makeDirectory(path: $actionsDir, recursive: true, force: true);

        $actions = [
            'AuthenticateCredentials' => 'authenticate-credentials.php',
            'IssueSession'            => 'issue-session.php',
            'ResolveUser'             => 'resolve-user.php',
        ];

        foreach ($actions as $actionName => $stubFile) {
            $stub = $this->files->get(path: __DIR__ . "/../../stubs/{$stubFile}");

            $stub = str_replace(
                search: ['{{MODEL_SHORT_NAME}}', '{{MODEL_CLASS}}', '{{NAMESPACE}}'],
                replace: [$config['model_short_name'], $config['model_class'], "App\\Actions\\Auth\\{$config['model_short_name']}"],
                subject: $stub
            );

            $target = "{$actionsDir}/{$actionName}.php";
            $this->files->put(path: $target, contents: $stub);
        }

        $this->line(string: "  ✓ app/Actions/Auth/{$config['model_short_name']}/ (" . count(value: $actions) . " actions)");
    }

    /**
     * Publish the model concern trait.
     *
     * @param  array<string, mixed>  $config
     */
    private function publishModelConcern(array $config): void
    {
        $stub = $this->files->get(path: __DIR__ . '/../../stubs/model-concern.php.stub');

        $stub = str_replace(
            search: ['{{MODEL_SHORT_NAME}}', '{{NAMESPACE}}', '{{TABLE_NAME}}'],
            replace: [$config['model_short_name'], "App\\Models\\Concerns", $config['table_name']],
            subject: $stub
        );

        $concernsDir = app_path(path: 'Models/Concerns');
        $this->files->makeDirectory(path: $concernsDir, recursive: true, force: true);

        $target = "{$concernsDir}/Has{$config['model_short_name']}Auth.php";
        $this->files->put(path: $target, contents: $stub);

        $this->line(string: "  ✓ app/Models/Concerns/Has{$config['model_short_name']}Auth.php");
    }

    /**
     * Publish test scaffolding.
     *
     * @param  array<string, mixed>  $config
     */
    private function publishTests(array $config): void
    {
        $testDir = base_path(path: "tests/Feature/Auth/{$config['model_short_name']}");
        $this->files->makeDirectory(path: $testDir, recursive: true, force: true);

        $stub = $this->files->get(path: __DIR__ . '/../../stubs/login-test.php.stub');

        $stub = str_replace(
            search: ['{{MODEL_SHORT_NAME}}', '{{MODEL_KEY}}', '{{ROUTE_PREFIX}}'],
            replace: [$config['model_short_name'], $config['model_key'], $config['route_prefix']],
            subject: $stub
        );

        $target = "{$testDir}/LoginTest.php";
        $this->files->put(path: $target, contents: $stub);

        $this->line(string: "  ✓ tests/Feature/Auth/{$config['model_short_name']}/LoginTest.php");
    }
}
