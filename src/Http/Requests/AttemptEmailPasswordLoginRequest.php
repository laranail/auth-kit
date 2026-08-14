<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Requests;

use Simtabi\Laranail\Auth\Support\AuthKit;
use Illuminate\Foundation\Http\FormRequest;

class AttemptEmailPasswordLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return array_merge([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ], [
            config(key: 'auth-kit.turnstile.input', default: 'cf-turnstile-response') => AuthKit::turnstileRules(),
        ]);
    }
}
