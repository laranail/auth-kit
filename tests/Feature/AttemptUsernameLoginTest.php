<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Actions\AttemptUsernameLogin;
use Simtabi\Laranail\Auth\Dtos\AttemptUsernameLoginInput;

it(description: 'returns passed when credentials are valid', closure: function () {
    $this->app['db']->connection()->getSchemaBuilder()->table(table: 'users', callback: function ($table) {
        $table->string('username')->unique()->after('name');
    });

    $user = User::factory()->create([
        'username' => 'ada',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptUsernameLogin::class);

    $result = $action->execute(new AttemptUsernameLoginInput(
        username: 'ada',
        password: 'secret',
        guard: 'web',
    ));

    expect($result->isPassed())->toBeTrue()
        ->and($result->user?->getAuthIdentifier())->toBe($user->getAuthIdentifier());
});

it(description: 'returns failed when credentials are wrong', closure: function () {
    $this->app['db']->connection()->getSchemaBuilder()->table(table: 'users', callback: function ($table) {
        $table->string('username')->unique()->after('name');
    });

    User::factory()->create([
        'username' => 'ada',
        'password' => bcrypt('secret'),
    ]);

    $action = app(AttemptUsernameLogin::class);

    $result = $action->execute(new AttemptUsernameLoginInput(
        username: 'ada',
        password: 'wrong',
        guard: 'web',
    ));

    expect($result->isPassed())->toBeFalse();
});
