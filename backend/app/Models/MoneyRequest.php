<?php

namespace App;

use App\Models\VCard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyRequest extends Model
{

    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'from_vcard',
        'to_vcard',
        'amount',
        'description',
        'status',
    ];

    // Define relationships
    public function fromVCard()
    {
        return $this->belongsTo(VCard::class, 'from_vcard', 'phone_number');
    }

    public function toVCard()
    {
        return $this->belongsTo(VCard::class, 'to_vcard', 'phone_number');
    }
}
