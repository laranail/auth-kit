<?php

declare(strict_types=1);

use Laravel\Prompts\Prompt;
use Laravel\Prompts\SelectPrompt;
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

    $exitCode = new InitAuthCommand()->handle(getAvailableModels: $getAvailableModels);

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

    $exitCode = new InitAuthCommand()->handle(getAvailableModels: $getAvailableModels);

    expect(value: $exitCode)->toBe(expected: Command::FAILURE)
        ->and(value: $output->fetch())->toContain('No models found in Models or models directory.');
});
