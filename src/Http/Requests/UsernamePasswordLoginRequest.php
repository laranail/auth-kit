<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsernamePasswordLoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
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
            'username.required' => 'The username field is required.',
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
            'username' => $this->string('username')->toString(),
            'password' => $this->string('password')->toString(),
        ];
    }

    /**
     * Determine if the user should be remembered.
     */
    public function remember(): bool
    {
        return $this->boolean('remember');
    }
}
