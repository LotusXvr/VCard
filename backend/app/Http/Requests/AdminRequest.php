<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('admin') ? $this->route('admin')->id : null;

        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $userId,
            'password' => 'required|string|min:3',
            'custom_option' => 'array',
            'custom_data' => 'array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.string' => 'Name must be a string',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email must be unique',
            'password.required' => 'Password is required',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password must be at least 3 characters long', // Mensagem para a regra 'min'
            'custom_option.array' => 'Custom option must be an array',
            'custom_data.array' => 'Custom data must be an array',
        ];
    }
}
