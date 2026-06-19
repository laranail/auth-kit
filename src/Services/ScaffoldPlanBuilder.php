<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Services;

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Auth\Dto\PlannedFile;
use Simtabi\Laranail\Auth\Dto\ScaffoldPlan;
use Simtabi\Laranail\Auth\Dto\ScaffoldTarget;

final class ScaffoldPlanBuilder
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly StubRenderer $stubRenderer,
    ) {
    }

    public function buildForNewModel(ScaffoldTarget $target, string $modelClass): ScaffoldPlan
    {
        $modelFile = $this->buildModelFile($target, $modelClass);
        $factoryFile = $this->buildFactoryFile($target, $modelClass);

        return new ScaffoldPlan(
            target: $target,
            modelClass: $modelClass,
            usingExistingModel: false,
            files: [$modelFile, $factoryFile],
        );
    }

    public function buildForExistingModel(ScaffoldTarget $target, string $existingModelClass): ScaffoldPlan
    {
        $className = class_basename($existingModelClass);
        $factoryFile = $this->buildFactoryFile($target, $className);

        return new ScaffoldPlan(
            target: $target,
            modelClass: $existingModelClass,
            usingExistingModel: true,
            files: [$factoryFile],
        );
    }

    private function buildModelFile(ScaffoldTarget $target, string $modelClass): PlannedFile
    {
        $absolutePath = base_path($target->modelPath . '/' . $modelClass . '.php');
        $exists = $this->files->exists($absolutePath);

        $contents = $this->stubRenderer->render('model.php.stub', [
            '{{ namespace }}' => $target->modelNamespace,
            '{{ class }}'     => $modelClass,
        ]);

        return new PlannedFile(
            path: $absolutePath,
            contents: $contents,
            description: $target->modelPath . '/' . $modelClass . '.php',
            exists: $exists,
            replace: $exists,
        );
    }

    private function buildFactoryFile(ScaffoldTarget $target, string $modelClass): PlannedFile
    {
        $factoryClass = $modelClass . 'Factory';
        $absolutePath = base_path($target->factoryPath . '/' . $factoryClass . '.php');
        $exists = $this->files->exists($absolutePath);

        $contents = $this->stubRenderer->render('factory.php.stub', [
            '{{ namespace }}'       => $target->factoryNamespace,
            '{{ model_namespace }}' => $target->modelNamespace,
            '{{ model_class }}'     => $modelClass,
            '{{ class }}'           => $factoryClass,
        ]);

        return new PlannedFile(
            path: $absolutePath,
            contents: $contents,
            description: $target->factoryPath . '/' . $factoryClass . '.php',
            exists: $exists,
            replace: $exists,
        );
    }
}
