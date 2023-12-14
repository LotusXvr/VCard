<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoneyRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'from_vcard' => $this->from_vcard,
            'to_vcard' => $this->to_vcard,
            'amount' => $this->amount,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
