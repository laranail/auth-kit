<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;

use Simtabi\Laranail\Auth\Enums\Stack;
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

    public static function validateModelClass(string $value): ?string
    {
        if (mb_trim($value) === '') {
            return 'The class name cannot be empty.';
        }

        if (str_contains(haystack: $value, needle: '/') || str_contains(haystack: $value, needle: '\\')) {
            return 'Provide only the class name, not a full path.';
        }

        if (preg_match(pattern: '/^[0-9]/', subject: $value)) {
            return 'The class name cannot start with a number.';
        }

        if (!preg_match(pattern: '/^[a-zA-Z_][a-zA-Z0-9_]*$/', subject: $value)) {
            return 'The class name may only contain letters, numbers, and underscores.';
        }

        $reserved = [
            'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch',
            'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do',
            'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach',
            'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final',
            'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if',
            'implements', 'include', 'include_once', 'instanceof', 'insteadof',
            'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print',
            'private', 'protected', 'public', 'readonly', 'require', 'require_once',
            'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use',
            'var', 'while', 'xor', 'yield', 'yield_from',
        ];

        if (in_array(needle: mb_strtolower(string: $value), haystack: $reserved, strict: true)) {
            return 'The class name cannot be a PHP reserved word.';
        }

        return null;
    }

    public function handle(): int
    {
        info(message: 'This command help you scaffold an authentication.');

        $stack = Stack::from(select(
            label: 'Which stack would you like to install?',
            options: Stack::options(),
        ));

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
            validate: fn (string $value): ?string => self::validateModelClass(value: $value),
        );

        $plan = $this->planBuilder->buildForNewModel(target: $target, modelClass: $modelClass, stack: $stack);

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
        $output = "========= Scaffolding auth on {$plan->target->label} (stack: {$plan->stack->value}) =========";

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
