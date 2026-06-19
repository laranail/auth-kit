<?php

declare(strict_types=1);

use Simtabi\Laranail\Auth\Services\ScaffoldTargetRepository;
use Simtabi\Laranail\Auth\Exceptions\InvalidScaffoldConfigException;

test(description: 'scaffold target repository returns configured targets', closure: function () {
    $repository = new ScaffoldTargetRepository();

    $targets = $repository->all();

    expect(value: $targets)->not->toBeEmpty()
        ->and(value: array_keys($targets))->toContain('root')
        ->and(value: array_keys($targets))->toContain('module_app_domain')
        ->and(value: array_keys($targets))->toContain('module_src');
});

test(description: 'scaffold target repository returns labels', closure: function () {
    $repository = new ScaffoldTargetRepository();

    $labels = $repository->labels();

    expect(value: $labels)->toBe(expected: [
        'root'              => 'Root application',
        'module_app_domain' => 'Module (app/Domain)',
        'module_src'        => 'Module (src)',
    ]);
});

test(description: 'scaffold target repository finds target by key', closure: function () {
    $repository = new ScaffoldTargetRepository();

    $target = $repository->find('root');

    expect(value: $target['type'])->toBe(expected: 'root')
        ->and(value: $target['label'])->toBe(expected: 'Root application');
});

test(description: 'scaffold target repository throws for invalid target key', closure: function () {
    $repository = new ScaffoldTargetRepository();

    $repository->find('nonexistent');
})->throws(InvalidScaffoldConfigException::class, 'Scaffold target [nonexistent] not found in configuration.');
