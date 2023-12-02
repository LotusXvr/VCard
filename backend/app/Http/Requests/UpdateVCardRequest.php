<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\Base64Services;

class UpdateVCardRequest extends FormRequest
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
        $vcard = $this->route('vcard');

        return [
            'phone_number' => 'unique:vcards,phone_number|digits:9|regex:/^9/' . $vcard->phone_number,
            'name' => 'required|string|max:30',
            'email' => '|email|unique:vcards,email' . $vcard->phone_number,
            'base64ImagePhoto' => 'nullable|string',
            'deletePhotoOnServer' => 'nullable|boolean',
        ];

    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Phone number is required',
            'phone_number.unique' => 'Phone number already exists',
            'phone_number.digits' => 'Phone number must be 9 digits',
            'phone_number.regex' => 'Phone number must start with 9', // portuguese rules
            'name.required' => 'Name is required',
            'name.max' => 'Name must be at most 30 characters',
            'email.required' => 'Email is required',
            'email.unique' => 'Email already exists',
            'confirmation_code.required' => 'Confirmation code is required',
            'confirmation_code.digits' => 'Confirmation code must be 4 digits',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $base64ImagePhoto = $this->base64ImagePhoto ?? null;
            if ($base64ImagePhoto) {
                $base64Service = new Base64Services();
                $mimeType = $base64Service->mimeType($base64ImagePhoto);
                if (!in_array($mimeType, ['image/png', 'image/jpg', 'image/jpeg'])) {
                    $validator->errors()->add('base64ImagePhoto', 'File type not supported (only supports "png" and "jpeg" images).');
                }
            }
        });
    }
}
