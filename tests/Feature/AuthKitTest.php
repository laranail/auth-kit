<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use Simtabi\Laranail\Auth\Tests\Fixtures\User;

test(description: 'has email login trait resolves user by email', closure: function () {
    $user = new User();
    $user->name = 'Test User';
    $user->email = 'test@example.com';
    $user->password = Hash::make(value: 'password');
    $user->save();

    $found = User::resolveByEmail(email: 'test@example.com');

    expect(value: $found)->not->toBeNull()
        ->and(value: $found->email)->toBe(expected: 'test@example.com');
});

test(description: 'has email login trait returns null for non-existent email', closure: function () {
    $found = User::resolveByEmail(email: 'nonexistent@example.com');

    expect(value: $found)->toBeNull();
});
