<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class VCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        Log::info('phone_number in VCardResource: ' . $this->phone_number);

        return [
            'phone_number' => $this->phone_number,
            'name' => $this->name,
            'email' => $this->email,
            'photo_url' => $this->photo_url,
            'password' => $this->password,
            'confirmation_code' => $this->confirmation_code,
            'blocked' => $this->blocked,
            'balance' => $this->balance,
            'savings' => $this->savings,
            'max_debit' => $this->max_debit,
        ];
    }
}
