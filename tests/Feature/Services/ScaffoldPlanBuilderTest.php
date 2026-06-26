<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Auth\Enums\Stack;
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
        migrationPath: 'database/migrations',
    );

    $builder = new ScaffoldPlanBuilder(new Filesystem(), new StubRenderer(new Filesystem()));
    $plan = $builder->buildForNewModel($target, 'Admin', Stack::Blade);

    expect(value: $plan)->toBeInstanceOf(ScaffoldPlan::class)
        ->and(value: $plan->modelClass)->toBe(expected: 'Admin')
        ->and(value: $plan->stack)->toBe(Stack::Blade)
        ->and(value: $plan->files)->toHaveCount(3);

    $modelFile = $plan->files[0];
    expect(value: $modelFile->description)->toBe(expected: 'app/Models/Admin.php')
        ->and(value: $modelFile->contents)->toContain('namespace App\\Models;')
        ->and(value: $modelFile->contents)->toContain('class Admin extends Authenticatable');

    $factoryFile = $plan->files[1];
    expect(value: $factoryFile->description)->toBe(expected: 'database/factories/AdminFactory.php')
        ->and(value: $factoryFile->contents)->toContain('namespace Database\\Factories;')
        ->and(value: $factoryFile->contents)->toContain('class AdminFactory extends Factory')
        ->and(value: $factoryFile->contents)->toContain('use App\\Models\\Admin;');

    $migrationFile = $plan->files[2];
    expect(value: $migrationFile->description)->toContain('database/migrations/')
        ->and(value: $migrationFile->description)->toContain('create_admins_table.php')
        ->and(value: $migrationFile->contents)->toContain("Schema::create('admins'")
        ->and(value: $migrationFile->contents)->toContain("Schema::dropIfExists('admins')");
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
        migrationPath: 'workbench/database/migrations',
    );

    $builder = new ScaffoldPlanBuilder(new Filesystem(), new StubRenderer(new Filesystem()));
    $plan = $builder->buildForNewModel($target, 'NonexistentUser', Stack::Blade);

    $modelFile = $plan->files[0];
    expect(value: $modelFile->exists)->toBeFalse()
        ->and(value: $modelFile->replace)->toBeFalse();
});

test(description: 'scaffold plan builder derives table name from model class', closure: function () {
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
        migrationPath: 'database/migrations',
    );

    $builder = new ScaffoldPlanBuilder(new Filesystem(), new StubRenderer(new Filesystem()));
    $plan = $builder->buildForNewModel($target, 'AdminUser', Stack::Blade);

    $migrationFile = $plan->files[2];
    expect(value: $migrationFile->description)->toContain('create_admin_users_table.php')
        ->and(value: $migrationFile->contents)->toContain("Schema::create('admin_users'");
});
