<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailPasswordLoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'The email field is required.',
            'email.email'       => 'Please provide a valid email address.',
            'password.required' => 'The password is required.',
        ];
    }

    /**
     * Get the authentication credentials.
     *
     * @return array<string, string>
     */
    public function credentials(): array
    {
        return [
            'email'    => $this->string(key: 'email')->toString(),
            'password' => $this->string(key: 'password')->toString(),
        ];
    }

    /**
     * Determine if the user should be remembered.
     */
    public function remember(): bool
    {
        return $this->boolean(key: 'remember');
    }
}
