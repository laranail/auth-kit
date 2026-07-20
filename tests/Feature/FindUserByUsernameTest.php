<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Actions\FindUserByUsername;
use Simtabi\Laranail\Auth\Dtos\FindUserByUsernameInput;

it(description: 'returns user when the username exists', closure: function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->table(table: 'users', callback: function ($table) {
        $table->string('username')->unique()->after('name');
    });

    $user = User::factory()->create([
        'username' => 'ada',
    ]);

    $action = app(FindUserByUsername::class);
    $input = new FindUserByUsernameInput(username: 'ada', guard: 'web');

    $found = $action->execute($input);

    expect($found)->not->toBeNull()
        ->and($found->username)->toBe('ada');
});

it(description: 'returns null when the username does not exist', closure: function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->table(table: 'users', callback: function ($table) {
        $table->string('username')->unique()->after('name');
    });

    $action = app(FindUserByUsername::class);
    $input = new FindUserByUsernameInput(username: 'nobody', guard: 'web');

    expect($action->execute($input))->toBeNull();
});

it(description: 'respects a custom guard', closure: function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->table(table: 'users', callback: function ($table) {
        $table->string('username')->unique()->after('name');
    });

    $user = User::factory()->create([
        'username' => 'ada',
    ]);

    $action = app(FindUserByUsername::class);
    $input = new FindUserByUsernameInput(
        username: 'ada',
        guard: config('auth-kit.guard'),
    );

    $found = $action->execute($input);

    expect($found)->not->toBeNull()
        ->and($found->username)->toBe('ada');
});
