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
        migrationPath: 'database/migrations',
    );

    $plan = new ScaffoldPlan(
        target: $target,
        modelClass: 'ExecutorTestModel',
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

test(description: 'scaffold executor skips existing files when replace is false', closure: function () {
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

    $filePath = base_path('app/Models/ExecutorSkipModel.php');
    file_put_contents($filePath, '<?php namespace App\\Models; class ExecutorSkipModel {}');

    $plan = new ScaffoldPlan(
        target: $target,
        modelClass: 'ExecutorSkipModel',
        files: [
            new PlannedFile(
                path: $filePath,
                contents: '<?php namespace App\\Models; class ExecutorSkipModel { REPLACED }',
                description: 'app/Models/ExecutorSkipModel.php',
                exists: true,
                replace: false,
            ),
        ],
    );

    $executor = new ScaffoldExecutor(new Filesystem());
    $written = $executor->execute($plan);

    expect(value: $written)->toHaveCount(0);

    $content = file_get_contents($filePath);
    expect(value: $content)->not->toContain('REPLACED');

    @unlink($filePath);
});

test(description: 'scaffold executor replaces existing files when replace is true', closure: function () {
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

    $filePath = base_path('app/Models/ExecutorReplaceModel.php');
    file_put_contents($filePath, '<?php namespace App\\Models; class ExecutorReplaceModel {}');

    $plan = new ScaffoldPlan(
        target: $target,
        modelClass: 'ExecutorReplaceModel',
        files: [
            new PlannedFile(
                path: $filePath,
                contents: '<?php namespace App\\Models; class ExecutorReplaceModel { REPLACED }',
                description: 'app/Models/ExecutorReplaceModel.php',
                exists: true,
                replace: true,
            ),
        ],
    );

    $executor = new ScaffoldExecutor(new Filesystem());
    $written = $executor->execute($plan);

    expect(value: $written)->toHaveCount(1);

    $content = file_get_contents($filePath);
    expect(value: $content)->toContain('REPLACED');

    @unlink($filePath);
});
