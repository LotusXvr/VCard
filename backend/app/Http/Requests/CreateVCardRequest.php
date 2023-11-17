<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVCardRequest extends FormRequest
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
        return [
            'phone_number' => 'required|string|unique:vcards,phone_number|digits:9',
            'password' => 'required|string|min:4|max:30',
            'name' => 'required|string|max:30',
            'email' => 'required|email|unique:vcards,email',
            'confirmation_code' => 'required|integer|digits:4',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => 'Phone number is required',
            'phone_number.unique' => 'Phone number already exists',
            'phone_number.digits' => 'Phone number must be 9 digits',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 4 characters',
            'password.max' => 'Password must be at most 30 characters',
            'name.required' => 'Name is required',
            'name.max' => 'Name must be at most 30 characters',
            'email.required' => 'Email is required',
            'email.unique' => 'Email already exists',
            'confirmation_code.required' => 'Confirmation code is required',
            'confirmation_code.digits' => 'Confirmation code must be 4 digits',
        ];
    }
}
