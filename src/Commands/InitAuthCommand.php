<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;

use Symfony\Component\Console\Attribute\AsCommand;
use Simtabi\Laranail\Auth\Enums\AuthScaffoldOption;
use Simtabi\Laranail\Auth\Actions\GetAvailableModels;

#[AsCommand(
    name: 'auth-kit:init',
    description: 'Scaffold a new authentication for your application. '
)]
class InitAuthCommand extends Command
{
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

        return self::SUCCESS;
    }
}
