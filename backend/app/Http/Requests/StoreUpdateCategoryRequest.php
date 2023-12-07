<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'vcard' => 'required|exists:vcards,phone_number',
            'type' => 'required|in:D,C',
            'name' => 'required|string|max:50',
        ];
    }
    public function messages()
    {
        return [
            'vcard.required' => 'The vcard field is required.',
            'vcard.exists' => 'The selected vcard is invalid.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The selected type is invalid.',
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than :max characters.',
        ];
    }
}
