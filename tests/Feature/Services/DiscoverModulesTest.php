<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Simtabi\Laranail\Auth\Services\DiscoverModules;

test(description: 'discover modules returns empty array when directory does not exist', closure: function () {
    $discover = new DiscoverModules(files: new Filesystem());

    $modules = $discover->all(modulesRoot: 'nonexistent-directory');

    expect(value: $modules)->toBeEmpty();
});

test(description: 'discover modules returns empty array when no modules exist', closure: function () {
    $discover = new DiscoverModules(files: new Filesystem());

    $modules = $discover->all(modulesRoot: 'database/migrations');

    expect(value: $modules)->toBeEmpty();
});
