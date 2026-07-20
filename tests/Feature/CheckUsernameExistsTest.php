<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Actions\CheckUsernameExists;
use Simtabi\Laranail\Auth\Dtos\CheckUsernameExistsInput;

it(description: 'returns true when the username exists', closure: function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->table(table: 'users', callback: function ($table) {
        $table->string('username')->unique()->after('name');
    });

    User::factory()->create([
        'username' => 'ada',
    ]);

    $action = app(CheckUsernameExists::class);
    $input = new CheckUsernameExistsInput(username: 'ada', guard: 'web');

    expect($action->execute($input))->toBeTrue();
});

it(description: 'returns false when the username does not exist', closure: function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->table(table: 'users', callback: function ($table) {
        $table->string('username')->unique()->after('name');
    });

    $action = app(CheckUsernameExists::class);
    $input = new CheckUsernameExistsInput(username: 'nobody', guard: 'web');

    expect($action->execute($input))->toBeFalse();
});

it(description: 'respects a custom guard', closure: function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->table(table: 'users', callback: function ($table) {
        $table->string('username')->unique()->after('name');
    });

    User::factory()->create([
        'username' => 'ada',
    ]);

    $action = app(CheckUsernameExists::class);
    $input = new CheckUsernameExistsInput(
        username: 'ada',
        guard: config('auth-kit.guard'),
    );

    expect($action->execute($input))->toBeTrue();
});
