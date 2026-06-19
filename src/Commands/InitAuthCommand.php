<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

use Simtabi\Laranail\Auth\Dto\ScaffoldPlan;
use Symfony\Component\Console\Attribute\AsCommand;
use Simtabi\Laranail\Auth\Services\DiscoverModules;
use Simtabi\Laranail\Auth\Services\ScaffoldExecutor;
use Simtabi\Laranail\Auth\Services\ScaffoldPlanBuilder;
use Simtabi\Laranail\Auth\Services\ScaffoldTargetResolver;
use Simtabi\Laranail\Auth\Services\ScaffoldTargetRepository;

#[AsCommand(
    name: 'auth-kit:init',
    description: 'Scaffold a new authentication for your application. '
)]
class InitAuthCommand extends Command
{
    public function __construct(
        private readonly ScaffoldTargetRepository $targets,
        private readonly ScaffoldTargetResolver $resolver,
        private readonly DiscoverModules $discoverModules,
        private readonly ScaffoldPlanBuilder $planBuilder,
        private readonly ScaffoldExecutor $executor,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        info(message: 'This command help you scaffold an authentication.');

        $targetLabels = $this->targets->labels();

        $targetKey = select(
            label: 'Where would you like to scaffold?',
            options: array_values(array: $targetLabels),
        );

        $selectedKey = array_search(needle: $targetKey, haystack: $targetLabels, strict: true);

        $targetConfig = $this->targets->find(key: $selectedKey);

        $moduleName = null;

        if ($targetConfig['type'] === 'module') {
            $modules = $this->discoverModules->all(modulesRoot: $targetConfig['modules_root']);

            if ($modules === []) {
                error(message: "No modules found in [{$targetConfig['modules_root']}/].");

                return self::FAILURE;
            }

            $moduleNames = array_column(array: $modules, column_key: 'name');

            $moduleName = select(
                label: 'Which module?',
                options: $moduleNames,
            );
        }

        $target = $this->resolver->resolve(key: $selectedKey, moduleName: $moduleName);

        $modelClass = text(
            label: 'Model class name',
            required: true,
            validate: fn (string $value): ?string => str_contains(haystack: $value, needle: '/') || str_contains(haystack: $value, needle: '\\')
                ? 'Provide only the class name, not a full path.'
                : null,
        );

        $plan = $this->planBuilder->buildForNewModel(target: $target, modelClass: $modelClass);

        $this->previewPlan(plan: $plan);

        if (! confirm(label: 'Continue?')) {
            info(message: 'Cancelled.');

            return self::SUCCESS;
        }

        $this->executor->execute(plan: $plan);

        $this->printSummary(plan: $plan);

        return self::SUCCESS;
    }

    private function previewPlan(ScaffoldPlan $plan): void
    {
        $output = "========= Scaffolding auth on {$plan->target->label} =========";

        foreach ($plan->files as $file) {
            $action = $file->exists ? 'REPLACE' : 'CREATE';
            $output .= "\n{$action} {$file->description}";
        }

        info($output);
    }

    private function printSummary(ScaffoldPlan $plan): void
    {
        info(message: 'Done!');

        foreach ($plan->files as $file) {
            $action = $file->exists ? 'Replaced' : 'Created';
            info(message: "  {$action} {$file->description}");
        }
    }
}
