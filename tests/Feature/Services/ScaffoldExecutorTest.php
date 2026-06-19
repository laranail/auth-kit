<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Auth\Dto\PlannedFile;
use Simtabi\Laranail\Auth\Dto\ScaffoldPlan;
use Simtabi\Laranail\Auth\Dto\ScaffoldTarget;
use Simtabi\Laranail\Auth\Services\ScaffoldExecutor;

test(description: 'scaffold executor creates missing directories and writes files', closure: function () {
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

    $plan = new ScaffoldPlan(
        target: $target,
        modelClass: 'ExecutorTestModel',
        usingExistingModel: false,
        files: [
            new PlannedFile(
                path: base_path('app/Models/ExecutorTestModel.php'),
                contents: '<?php namespace App\\Models; class ExecutorTestModel {}',
                description: 'app/Models/ExecutorTestModel.php',
                exists: false,
                replace: false,
            ),
        ],
    );

    $executor = new ScaffoldExecutor(new Filesystem());
    $written = $executor->execute($plan);

    expect(value: $written)->toHaveCount(1)
        ->and(value: base_path('app/Models/ExecutorTestModel.php'))->toBeFile();

    $content = file_get_contents(base_path('app/Models/ExecutorTestModel.php'));
    expect(value: $content)->toContain('class ExecutorTestModel');

    @unlink(base_path('app/Models/ExecutorTestModel.php'));
});
