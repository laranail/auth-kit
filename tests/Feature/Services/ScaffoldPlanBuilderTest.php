<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Auth\Dto\ScaffoldPlan;
use Simtabi\Laranail\Auth\Dto\ScaffoldTarget;
use Simtabi\Laranail\Auth\Services\StubRenderer;
use Simtabi\Laranail\Auth\Services\ScaffoldPlanBuilder;

test(description: 'scaffold plan builder builds plan for new model', closure: function () {
    $target = new ScaffoldTarget(
        key: 'root',
        label: 'Root application',
        type: 'root',
        moduleName: null,
        modulePath: null,
        basePath: '',
        sourcePath: 'app',
        modelPath: 'app/Models',
        modelNamespace: 'App\\Models',
        factoryPath: 'database/factories',
        factoryNamespace: 'Database\\Factories',
    );

    $builder = new ScaffoldPlanBuilder(new Filesystem(), new StubRenderer(new Filesystem()));
    $plan = $builder->buildForNewModel($target, 'Admin');

    expect(value: $plan)->toBeInstanceOf(ScaffoldPlan::class)
        ->and(value: $plan->modelClass)->toBe(expected: 'Admin')
        ->and(value: $plan->usingExistingModel)->toBeFalse()
        ->and(value: $plan->files)->toHaveCount(2);

    $modelFile = $plan->files[0];
    expect(value: $modelFile->description)->toBe(expected: 'app/Models/Admin.php')
        ->and(value: $modelFile->contents)->toContain('namespace App\\Models;')
        ->and(value: $modelFile->contents)->toContain('class Admin extends Authenticatable');

    $factoryFile = $plan->files[1];
    expect(value: $factoryFile->description)->toBe(expected: 'database/factories/AdminFactory.php')
        ->and(value: $factoryFile->contents)->toContain('namespace Database\\Factories;')
        ->and(value: $factoryFile->contents)->toContain('class AdminFactory extends Factory')
        ->and(value: $factoryFile->contents)->toContain('use App\\Models\\Admin;');
});

test(description: 'scaffold plan builder builds plan for existing model', closure: function () {
    $target = new ScaffoldTarget(
        key: 'root',
        label: 'Root application',
        type: 'root',
        moduleName: null,
        modulePath: null,
        basePath: '',
        sourcePath: 'app',
        modelPath: 'app/Models',
        modelNamespace: 'App\\Models',
        factoryPath: 'database/factories',
        factoryNamespace: 'Database\\Factories',
    );

    $builder = new ScaffoldPlanBuilder(new Filesystem(), new StubRenderer(new Filesystem()));
    $plan = $builder->buildForExistingModel($target, 'Admin');

    expect(value: $plan->usingExistingModel)->toBeTrue()
        ->and(value: $plan->modelClass)->toBe(expected: 'Admin')
        ->and(value: $plan->files)->toHaveCount(1);

    $factoryFile = $plan->files[0];
    expect(value: $factoryFile->description)->toBe(expected: 'database/factories/AdminFactory.php')
        ->and(value: $factoryFile->contents)->toContain('namespace Database\\Factories;')
        ->and(value: $factoryFile->contents)->toContain('use App\\Models\\Admin;');
});

test(description: 'scaffold plan builder marks existing files as replace', closure: function () {
    $target = new ScaffoldTarget(
        key: 'root',
        label: 'Root application',
        type: 'root',
        moduleName: null,
        modulePath: null,
        basePath: '',
        sourcePath: 'app',
        modelPath: 'workbench/app/Models',
        modelNamespace: 'Workbench\\App\\Models',
        factoryPath: 'workbench/database/factories',
        factoryNamespace: 'Workbench\\Database\\Factories',
    );

    $builder = new ScaffoldPlanBuilder(new Filesystem(), new StubRenderer(new Filesystem()));
    $plan = $builder->buildForNewModel($target, 'NonexistentUser');

    $modelFile = $plan->files[0];
    expect(value: $modelFile->exists)->toBeFalse()
        ->and(value: $modelFile->replace)->toBeFalse();
});
