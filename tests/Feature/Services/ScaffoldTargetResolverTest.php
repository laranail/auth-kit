<?php

declare(strict_types=1);

use Simtabi\Laranail\Auth\Dto\ScaffoldTarget;
use Simtabi\Laranail\Auth\Services\DiscoverModules;
use Simtabi\Laranail\Auth\Services\ScaffoldTargetResolver;
use Simtabi\Laranail\Auth\Services\ScaffoldTargetRepository;

test(description: 'scaffold target resolver resolves root target correctly', closure: function () {
    $repository = new ScaffoldTargetRepository();
    $resolver = new ScaffoldTargetResolver($repository, new DiscoverModules(app('files')));

    $target = $resolver->resolve('root');

    expect(value: $target)->toBeInstanceOf(ScaffoldTarget::class)
        ->and(value: $target->key)->toBe(expected: 'root')
        ->and(value: $target->type)->toBe(expected: 'root')
        ->and(value: $target->moduleName)->toBeNull()
        ->and(value: $target->modelNamespace)->toBe(expected: 'App\\Models')
        ->and(value: $target->factoryNamespace)->toBe(expected: 'Database\\Factories')
        ->and(value: $target->migrationPath)->toBe(expected: 'database/migrations');
});

test(description: 'scaffold target resolver throws for invalid key', closure: function () {
    $repository = new ScaffoldTargetRepository();
    $resolver = new ScaffoldTargetResolver($repository, new DiscoverModules(app('files')));

    $resolver->resolve('nonexistent');
})->throws(InvalidArgumentException::class, 'Scaffold target [nonexistent] not found in configuration.');

test(description: 'scaffold target resolver throws when module name missing for module target', closure: function () {
    $repository = new ScaffoldTargetRepository();
    $resolver = new ScaffoldTargetResolver($repository, new DiscoverModules(app('files')));

    $resolver->resolve('module_app_domain');
})->throws(InvalidArgumentException::class, 'Module name is required for scaffold target [module_app_domain].');
