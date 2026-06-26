<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Services;

use Illuminate\Support\Str;
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
        $modelFile = $this->buildModelFile(target: $target, modelClass: $modelClass);
        $factoryFile = $this->buildFactoryFile(target: $target, modelClass: $modelClass);
        $migrationFile = $this->buildMigrationFile(target: $target, modelClass: $modelClass);

        return new ScaffoldPlan(
            target: $target,
            modelClass: $modelClass,
            files: [$modelFile, $factoryFile, $migrationFile],
        );
    }

    private function buildModelFile(ScaffoldTarget $target, string $modelClass): PlannedFile
    {
        $absolutePath = base_path($target->modelPath . '/' . $modelClass . '.php');
        $exists = $this->files->exists($absolutePath);

        $contents = $this->stubRenderer->render(stubName: 'model.php.stub', variables: [
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

        $contents = $this->stubRenderer->render(stubName: 'factory.php.stub', variables: [
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

    private function buildMigrationFile(ScaffoldTarget $target, string $modelClass): PlannedFile
    {
        $table = Str::snake(Str::pluralStudly($modelClass));
        $suffix = '_create_' . $table . '_table.php';
        $timestamp = time();

        do {
            $filename = date('Y_m_d_His', $timestamp) . $suffix;
            $relativePath = $target->migrationPath . '/' . $filename;
            $absolutePath = base_path($relativePath);
            $exists = $this->files->exists($absolutePath);

            if ($exists) {
                $timestamp++;
            }
        } while ($exists);

        $contents = $this->stubRenderer->render(stubName: 'migration.php.stub', variables: [
            '{{ table }}' => $table,
        ]);

        return new PlannedFile(
            path: $absolutePath,
            contents: $contents,
            description: $relativePath,
            exists: $exists,
            replace: $exists,
        );
    }
}
