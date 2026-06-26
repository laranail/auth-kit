<?php

declare(strict_types=1);

use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextPrompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\ConfirmPrompt;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Simtabi\Laranail\Auth\Commands\InitAuthCommand;
use Symfony\Component\Console\Output\BufferedOutput;

test(description: 'init auth command has the expected command metadata', closure: function () {
    $attribute = new ReflectionClass(objectOrClass: InitAuthCommand::class)
        ->getAttributes(name: AsCommand::class)[0]
        ->newInstance();

    expect(value: $attribute->name)->toBe(expected: 'auth-kit:init')
        ->and(value: $attribute->description)->toBe(expected: 'Scaffold a new authentication for your application. ');
});

test(description: 'init auth command creates a new model for root target', closure: function () {
    $files = app(abstract: Filesystem::class);
    $modelFile = base_path(path: 'app/Models/Admin.php');
    $factoryFile = base_path(path: 'database/factories/AdminFactory.php');

    @unlink(filename: $modelFile);
    @unlink(filename: $factoryFile);

    $output = new BufferedOutput();
    $selections = [
        'blade',
        'Root application',
    ];
    $textInputs = [
        'Admin',
    ];
    $prompts = [];

    Prompt::setOutput(output: $output);
    Prompt::fallbackWhen(condition: true);

    SelectPrompt::fallbackUsing(fallback: function (SelectPrompt $prompt) use (&$selections, &$prompts): string {
        $prompts[] = [
            'label'   => $prompt->label,
            'options' => $prompt->options,
        ];

        return array_shift(array: $selections);
    });

    TextPrompt::fallbackUsing(fallback: function (TextPrompt $prompt) use (&$textInputs, &$prompts): string {
        $prompts[] = [
            'label'   => $prompt->label,
            'default' => $prompt->default,
        ];

        return array_shift(array: $textInputs);
    });

    ConfirmPrompt::fallbackUsing(fallback: fn (): true => true);

    $exitCode = app(abstract: InitAuthCommand::class)->handle();

    $outputContent = $output->fetch();
    expect(value: $exitCode)->toBe(expected: Command::SUCCESS)
        ->and(value: $outputContent)->toContain('This command help you scaffold an authentication.')
        ->and(value: $outputContent)->toContain('Created app/Models/Admin.php')
        ->and(value: $outputContent)->toContain('Created database/factories/AdminFactory.php')
        ->and(value: $outputContent)->toContain('create_admins_table.php')
        ->and(value: $prompts)->toBe(expected: [
            [
                'label'   => 'Which stack would you like to install?',
                'options' => ['blade' => 'Blade'],
            ],
            [
                'label'   => 'Where would you like to scaffold?',
                'options' => ['Root application', 'Module (app/Domain)', 'Module (src)'],
            ],
            [
                'label'   => 'Model class name',
                'default' => '',
            ],
        ])
        ->and(value: $modelFile)->toBeFile();

    $modelContent = file_get_contents(filename: $modelFile);
    expect(value: $modelContent)->toContain('namespace App\\Models;')
        ->and(value: $modelContent)->toContain('class Admin extends Authenticatable')
        ->and(value: $factoryFile)->toBeFile();

    $factoryContent = file_get_contents(filename: $factoryFile);
    expect(value: $factoryContent)->toContain('namespace Database\\Factories;')
        ->and(value: $factoryContent)->toContain('class AdminFactory extends Factory')
        ->and(value: $factoryContent)->toContain('use App\\Models\\Admin;');

    @unlink(filename: $modelFile);
    @unlink(filename: $factoryFile);

    $migrationFiles = glob(pattern: base_path(path: 'database/migrations/*_create_admins_table.php'));
    foreach ($migrationFiles as $migrationFile) {
        @unlink(filename: $migrationFile);
    }
});

test(description: 'init auth command replaces existing model for root target', closure: function () {
    $files = app(abstract: Filesystem::class);
    $modelPath = base_path(path: 'app/Models/Replaceable.php');
    $factoryPath = base_path(path: 'database/factories/ReplaceableFactory.php');

    $files->ensureDirectoryExists(path: dirname(path: $modelPath));
    $files->put(path: $modelPath, contents: '<?php namespace App\\Models; class Replaceable {}');

    @unlink(filename: $factoryPath);

    $output = new BufferedOutput();
    $selections = [
        'blade',
        'Root application',
    ];
    $textInputs = [
        'Replaceable',
    ];

    Prompt::setOutput(output: $output);
    Prompt::fallbackWhen(condition: true);

    SelectPrompt::fallbackUsing(fallback: function (SelectPrompt $prompt) use (&$selections): string {
        return array_shift(array: $selections);
    });

    TextPrompt::fallbackUsing(fallback: function (TextPrompt $prompt) use (&$textInputs): string {
        return array_shift(array: $textInputs);
    });

    ConfirmPrompt::fallbackUsing(fallback: fn (): true => true);

    $exitCode = app(abstract: InitAuthCommand::class)->handle();

    $outputContent = $output->fetch();
    expect(value: $exitCode)->toBe(expected: Command::SUCCESS)
        ->and(value: $outputContent)->toContain('Replaced app/Models/Replaceable.php');

    $modelContent = file_get_contents(filename: $modelPath);
    expect(value: $modelContent)->toContain('namespace App\\Models;')
        ->and(value: $modelContent)->toContain('class Replaceable extends Authenticatable');

    @unlink(filename: $modelPath);
    @unlink(filename: $factoryPath);

    $migrationFiles = glob(pattern: base_path(path: 'database/migrations/*_create_replaceables_table.php'));
    foreach ($migrationFiles as $migrationFile) {
        @unlink(filename: $migrationFile);
    }
});

test(description: 'init auth command fails when no modules found for module target', closure: function () {
    $output = new BufferedOutput();
    $selections = [
        'blade',
        'Module (app/Domain)',
    ];

    $originalConfig = config('auth-kit.targets');
    config(['auth-kit.targets.module_app_domain.modules_root' => 'nonexistent-modules-dir']);

    Prompt::setOutput(output: $output);
    Prompt::fallbackWhen(condition: true);

    SelectPrompt::fallbackUsing(fallback: function (SelectPrompt $prompt) use (&$selections): string {
        return array_shift(array: $selections);
    });

    try {
        $exitCode = app(abstract: InitAuthCommand::class)->handle();
    } finally {
        config(['auth-kit.targets' => $originalConfig]);
    }

    expect(value: $exitCode)->toBe(expected: Command::FAILURE)
        ->and(value: $output->fetch())->toContain('No modules found');
});

test(description: 'init auth command scaffolds for module target', closure: function () {
    $output = new BufferedOutput();
    $selections = [
        'blade',
        'Module (app/Domain)',
        'CRM',
    ];
    $textInputs = [
        'Customer',
    ];

    app(abstract: Filesystem::class)->ensureDirectoryExists(path: base_path(path: 'modules/CRM'));

    Prompt::setOutput(output: $output);
    Prompt::fallbackWhen(condition: true);

    SelectPrompt::fallbackUsing(fallback: function (SelectPrompt $prompt) use (&$selections): string {
        return array_shift(array: $selections);
    });

    TextPrompt::fallbackUsing(fallback: function (TextPrompt $prompt) use (&$textInputs): string {
        return array_shift(array: $textInputs);
    });

    ConfirmPrompt::fallbackUsing(fallback: fn (): true => true);

    $exitCode = app(abstract: InitAuthCommand::class)->handle();

    $outputContent = $output->fetch();
    expect(value: $exitCode)->toBe(expected: Command::SUCCESS)
        ->and(value: $outputContent)->toContain('Done!')
        ->and(value: $outputContent)->toContain('Created modules/CRM/app/Models/Customer.php')
        ->and(value: $outputContent)->toContain('Created modules/CRM/database/factories/CustomerFactory.php')
        ->and(value: $outputContent)->toContain('create_customers_table.php');

    @unlink(filename: base_path(path: 'modules/CRM/app/Models/Customer.php'));
    @unlink(filename: base_path(path: 'modules/CRM/database/factories/CustomerFactory.php'));
    @rmdir(base_path(path: 'modules/CRM/database/factories'));

    $migrationFiles = glob(pattern: base_path(path: 'modules/CRM/database/migrations/*_create_customers_table.php'));
    foreach ($migrationFiles as $migrationFile) {
        @unlink(filename: $migrationFile);
    }
    @rmdir(base_path(path: 'modules/CRM/database/migrations'));
    @rmdir(base_path(path: 'modules/CRM/database'));
    @rmdir(base_path(path: 'modules/CRM/app/Models'));
    @rmdir(base_path(path: 'modules/CRM/app'));
    @rmdir(base_path(path: 'modules/CRM'));
});

test(description: 'init auth command cancels when user declines confirmation', closure: function () {
    $files = app(abstract: Filesystem::class);
    $modelFile = base_path(path: 'app/Models/Admin.php');
    $factoryFile = base_path(path: 'database/factories/AdminFactory.php');

    @unlink(filename: $modelFile);
    @unlink(filename: $factoryFile);

    $output = new BufferedOutput();
    $selections = [
        'blade',
        'Root application',
    ];
    $textInputs = [
        'Admin',
    ];

    Prompt::setOutput(output: $output);
    Prompt::fallbackWhen(condition: true);

    SelectPrompt::fallbackUsing(fallback: function (SelectPrompt $prompt) use (&$selections): string {
        return array_shift(array: $selections);
    });

    TextPrompt::fallbackUsing(fallback: function (TextPrompt $prompt) use (&$textInputs): string {
        return array_shift(array: $textInputs);
    });

    ConfirmPrompt::fallbackUsing(fallback: fn (): false => false);

    $exitCode = app(abstract: InitAuthCommand::class)->handle();

    $outputContent = $output->fetch();
    expect(value: $exitCode)->toBe(expected: Command::SUCCESS)
        ->and(value: $outputContent)->toContain('Cancelled.')
        ->and(value: $modelFile)->not->toBeFile()
        ->and(value: $factoryFile)->not->toBeFile();
});

test(description: 'validateModelClass rejects empty string', closure: function () {
    expect(value: InitAuthCommand::validateModelClass(''))->toBe(expected: 'The class name cannot be empty.')
        ->and(value: InitAuthCommand::validateModelClass('  '))->toBe(expected: 'The class name cannot be empty.');
});

test(description: 'validateModelClass rejects paths with slashes', closure: function () {
    expect(value: InitAuthCommand::validateModelClass('App/Models/User'))->toBe(expected: 'Provide only the class name, not a full path.')
        ->and(value: InitAuthCommand::validateModelClass('App\\Models\\User'))->toBe(expected: 'Provide only the class name, not a full path.');
});

test(description: 'validateModelClass rejects names starting with a number', closure: function () {
    expect(value: InitAuthCommand::validateModelClass('1User'))->toBe(expected: 'The class name cannot start with a number.')
        ->and(value: InitAuthCommand::validateModelClass('0Test'))->toBe(expected: 'The class name cannot start with a number.');
});

test(description: 'validateModelClass rejects names with special characters', closure: function () {
    expect(value: InitAuthCommand::validateModelClass('Admin-User'))->toBe(expected: 'The class name may only contain letters, numbers, and underscores.')
        ->and(value: InitAuthCommand::validateModelClass('Admin User'))->toBe(expected: 'The class name may only contain letters, numbers, and underscores.')
        ->and(value: InitAuthCommand::validateModelClass('Admin@User'))->toBe(expected: 'The class name may only contain letters, numbers, and underscores.');
});

test(description: 'validateModelClass rejects PHP reserved words', closure: function () {
    expect(value: InitAuthCommand::validateModelClass('CLASS'))->toBe(expected: 'The class name cannot be a PHP reserved word.')
        ->and(value: InitAuthCommand::validateModelClass('New'))->toBe(expected: 'The class name cannot be a PHP reserved word.');
});

test(description: 'validateModelClass rejects lowercase names', closure: function () {
    expect(value: InitAuthCommand::validateModelClass('class'))->toBe(expected: 'The class name must start with an uppercase letter (PascalCase).')
        ->and(value: InitAuthCommand::validateModelClass('new'))->toBe(expected: 'The class name must start with an uppercase letter (PascalCase).')
        ->and(value: InitAuthCommand::validateModelClass('function'))->toBe(expected: 'The class name must start with an uppercase letter (PascalCase).')
        ->and(value: InitAuthCommand::validateModelClass('user'))->toBe(expected: 'The class name must start with an uppercase letter (PascalCase).');
});

test(description: 'validateModelClass accepts valid class names', closure: function () {
    expect(value: InitAuthCommand::validateModelClass('User'))->toBeNull()
        ->and(value: InitAuthCommand::validateModelClass('Admin'))->toBeNull()
        ->and(value: InitAuthCommand::validateModelClass('UserProfile'))->toBeNull()
        ->and(value: InitAuthCommand::validateModelClass('_Private'))->toBeNull()
        ->and(value: InitAuthCommand::validateModelClass('User2'))->toBeNull()
        ->and(value: InitAuthCommand::validateModelClass('FooBar123'))->toBeNull();
});
