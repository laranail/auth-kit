<?php

declare(strict_types=1);

use Simtabi\Laranail\Auth\Http\Requests\EmailPasswordLoginRequest;
use Simtabi\Laranail\Auth\Http\Requests\UsernamePasswordLoginRequest;

test(description: 'email password login request validates email field', closure: function () {
    $request = new EmailPasswordLoginRequest();
    expect($request->rules())->toHaveKeys(['email', 'password', 'remember']);
});

test(description: 'email password login request requires email to be valid', closure: function () {
    $rules = (new EmailPasswordLoginRequest())->rules();
    expect($rules['email'])->toContain('email');
});

test(description: 'email password login request requires password', closure: function () {
    $rules = (new EmailPasswordLoginRequest())->rules();
    expect($rules['password'])->toContain('required');
});

test(description: 'email password login request has custom messages', closure: function () {
    $messages = (new EmailPasswordLoginRequest())->messages();
    expect($messages)->toHaveKey('email.required');
    expect($messages)->toHaveKey('password.required');
});

test(description: 'username password login request validates username field', closure: function () {
    $request = new UsernamePasswordLoginRequest();
    expect($request->rules())->toHaveKeys(['username', 'password', 'remember']);
});

test(description: 'username password login request requires username to be string', closure: function () {
    $rules = (new UsernamePasswordLoginRequest())->rules();
    expect($rules['username'])->toContain('string');
});

test(description: 'username password login request requires password', closure: function () {
    $rules = (new UsernamePasswordLoginRequest())->rules();
    expect($rules['password'])->toContain('required');
});

test(description: 'username password login request has custom messages', closure: function () {
    $messages = (new UsernamePasswordLoginRequest())->messages();
    expect($messages)->toHaveKey('username.required');
    expect($messages)->toHaveKey('password.required');
});
