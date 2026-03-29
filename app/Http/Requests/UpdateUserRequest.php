<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->route('user'),
            'password' => 'sometimes|string|min:6',
            'bio' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Name must be a valid string',
            'name.max' => 'Name must not exceed 255 characters',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'This email is already taken',
            'password.min' => 'Password must be at least 6 characters',
            'bio.string' => 'Bio must be text',
            'bio.max' => 'Bio must not exceed 1000 characters',
        ];
    }
}
