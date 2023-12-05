<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\VCard;

class UpdateVcardCodeRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'current_confirmation_code' => 'required', 'digits:4',
            'confirmation_code' => 'required|digits:4|integer',
            'confirmation_code_confirmation' => 'required|same:confirmation_code',
        ];
    }

    public function messages() {
        return [
            'current_confirmation_code.required' => 'The current confirmation code is required.',
            'current_confirmation_code.digits' => 'The current confirmation code must be 4 digits.',
            'confirmation_code.required' => 'The confirmation code is required.',
            'confirmation_code.digits' => 'The confirmation code must be 4 digits.',
            'confirmation_code.integer' => 'The confirmation code must be an integer.',
            'confirmation_code_confirmation.required' => 'The confirmation code confirmation is required.',
            'confirmation_code_confirmation.same' => 'The confirmation code confirmation must match the confirmation code.',
        ];
    }

    public function attributes() {
        return [
            'current_confirmation_code' => 'current confirmation code',
            'confirmation_code' => 'confirmation code',
            'confirmation_code_confirmation' => 'confirmation code confirmation',
        ];
    }

}
