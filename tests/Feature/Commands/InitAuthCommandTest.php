<?php

declare(strict_types=1);

use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextPrompt;
use Laravel\Prompts\SelectPrompt;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Simtabi\Laranail\Auth\Commands\InitAuthCommand;
use Simtabi\Laranail\Auth\Enums\AuthScaffoldOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Simtabi\Laranail\Auth\Actions\GetAvailableModels;

test(description: 'init auth command has the expected command metadata', closure: function () {
    $attribute = new ReflectionClass(objectOrClass: InitAuthCommand::class)
        ->getAttributes(name: AsCommand::class)[0]
        ->newInstance();

    expect(value: $attribute->name)->toBe(expected: 'auth-kit:init')
        ->and(value: $attribute->description)->toBe(expected: 'Scaffold a new authentication for your application. ');
});

test(description: 'init auth command lets the user select an existing model', closure: function () {
    $output = new BufferedOutput();
    $selections = [
        AuthScaffoldOption::USE_EXISTING_MODEL->value,
        'App\\Models\\User',
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

    $getAvailableModels = Mockery::mock(GetAvailableModels::class);
    $getAvailableModels->shouldReceive('__invoke')
        ->once()
        ->andReturn(['App\\Models\\User']);

    $exitCode = app(abstract: InitAuthCommand::class)->handle(getAvailableModels: $getAvailableModels);

    expect(value: $exitCode)->toBe(expected: Command::SUCCESS)
        ->and(value: $output->fetch())->toContain('This command help you scaffold an authentication.')
        ->and(value: $prompts)->toBe(expected: [
            [
                'label'   => AuthScaffoldOption::description(),
                'options' => AuthScaffoldOption::labels(),
            ],
            [
                'label'   => 'Which model would you like to scaffold authentication for?',
                'options' => ['App\\Models\\User'],
            ],
        ]);
});

test(description: 'init auth command fails when no existing models are available', closure: function () {
    $output = new BufferedOutput();
    $selections = [AuthScaffoldOption::USE_EXISTING_MODEL->value];
    Prompt::setOutput(output: $output);
    Prompt::fallbackWhen(condition: true);
    SelectPrompt::fallbackUsing(fallback: fn (): string => array_shift(array: $selections));

    $getAvailableModels = Mockery::mock(GetAvailableModels::class);
    $getAvailableModels->shouldReceive('__invoke')
        ->once()
        ->andReturn([]);

    $exitCode = app(abstract: InitAuthCommand::class)->handle(getAvailableModels: $getAvailableModels);

    expect(value: $exitCode)->toBe(expected: Command::FAILURE)
        ->and(value: $output->fetch())->toContain('No models found in Models or models directory.');
});

test(description: 'init auth command creates a new model from path', closure: function () {
    $files = app(abstract: Filesystem::class);
    $modelFile = base_path(path: 'workbench/app/Models/Admin.php');
    $factoryFile = base_path(path: 'database/factories/AdminFactory.php');

    @unlink(filename: $modelFile);
    @unlink(filename: $factoryFile);

    $output = new BufferedOutput();
    $selections = [
        AuthScaffoldOption::CREATE_NEW_MODEL->value,
    ];
    $textInputs = [
        'workbench/app/Models/Admin',
        'Workbench\\App\\Models',
    ];
    $prompts = [];

    Prompt::setOutput(output: $output);
    Prompt::fallbackWhen(condition: true);

    SelectPrompt::fallbackUsing(fallback: function (SelectPrompt $prompt) use (&$selections): string {
        return array_shift(array: $selections);
    });

    TextPrompt::fallbackUsing(fallback: function (TextPrompt $prompt) use (&$textInputs, &$prompts): string {
        $prompts[] = [
            'label'   => $prompt->label,
            'default' => $prompt->default,
        ];

        return array_shift(array: $textInputs);
    });

    $getAvailableModels = Mockery::mock(GetAvailableModels::class);
    $getAvailableModels->shouldNotReceive('__invoke');

    $exitCode = app(abstract: InitAuthCommand::class)->handle(getAvailableModels: $getAvailableModels);

    $outputContent = $output->fetch();
    expect(value: $exitCode)->toBe(expected: Command::SUCCESS)
        ->and(value: $outputContent)->toContain('Model created at workbench/app/Models/Admin.php')
        ->and(value: $outputContent)->toContain('Factory created at database/factories/AdminFactory.php')
        ->and(value: $prompts)->toBe(expected: [
            [
                'label' => 'Enter the model path (e.g., app/Models/User)',
                'default' => '',
            ],
            [
                'label' => 'Confirm the model namespace',
                'default' => 'Workbench\\App\\Models',
            ],
        ])
        ->and(value: $modelFile)->toBeFile();

    $modelContent = file_get_contents(filename: $modelFile);
    expect(value: $modelContent)->toContain('namespace Workbench\\App\\Models;')
        ->and(value: $modelContent)->toContain('class Admin extends Authenticatable')
        ->and(value: $factoryFile)->toBeFile();

    $factoryContent = file_get_contents(filename: $factoryFile);
    expect(value: $factoryContent)->toContain('namespace Database\\Factories;')
        ->and(value: $factoryContent)->toContain('class AdminFactory extends Factory')
        ->and(value: $factoryContent)->toContain('use Workbench\\App\\Models\\Admin;');

    @unlink(filename: $modelFile);
    @unlink(filename: $factoryFile);
});

test(description: 'init auth command replaces existing model', closure: function () {
    $files = app(abstract: Filesystem::class);
    $modelPath = base_path(path: 'workbench/app/Models/Replaceable.php');
    $factoryPath = base_path(path: 'database/factories/ReplaceableFactory.php');

    $files->ensureDirectoryExists(path: dirname(path: $modelPath));
    $files->put(path: $modelPath, contents: '<?php namespace Workbench\\App\\Models; class Replaceable {}');

    @unlink(filename: $factoryPath);

    $output = new BufferedOutput();
    $selections = [
        AuthScaffoldOption::CREATE_NEW_MODEL->value,
    ];
    $textInputs = [
        'workbench/app/Models/Replaceable',
        'Workbench\\App\\Models',
    ];

    Prompt::setOutput(output: $output);
    Prompt::fallbackWhen(condition: true);

    SelectPrompt::fallbackUsing(fallback: function (SelectPrompt $prompt) use (&$selections): string {
        return array_shift(array: $selections);
    });

    TextPrompt::fallbackUsing(fallback: function (TextPrompt $prompt) use (&$textInputs): string {
        return array_shift(array: $textInputs);
    });

    $getAvailableModels = Mockery::mock(GetAvailableModels::class);
    $getAvailableModels->shouldNotReceive('__invoke');

    $exitCode = app(abstract: InitAuthCommand::class)->handle(getAvailableModels: $getAvailableModels);

    $outputContent = $output->fetch();
    expect(value: $exitCode)->toBe(expected: Command::SUCCESS)
        ->and(value: $outputContent)->toContain('Model updated at workbench/app/Models/Replaceable.php');

    $modelContent = file_get_contents(filename: $modelPath);
    expect(value: $modelContent)->toContain('namespace Workbench\\App\\Models;')
        ->and(value: $modelContent)->toContain('class Replaceable extends Authenticatable');

    @unlink(filename: $modelPath);
    @unlink(filename: $factoryPath);
});
