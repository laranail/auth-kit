<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules for the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get the model class for the current route.
     */
    public function getAuthModel(): string
    {
        $modelKey = $this->getModelKey();

        return config(key: "auth-kit.models.{$modelKey}.model");
    }

    /**
     * Get the model key from the route name.
     */
    public function getModelKey(): string
    {
        $routeName = $this->route()?->getName() ?? '';
        $parts = explode(separator: '.', string: $routeName);

        return $parts[0] ?? 'user';
    }
}
