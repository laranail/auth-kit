<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckUsernameExistsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
        ];
    }
}
