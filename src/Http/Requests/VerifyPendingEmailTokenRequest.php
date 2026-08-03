<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPendingEmailTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'token' => ['required', 'string', 'max:255'],
        ];
    }
}
