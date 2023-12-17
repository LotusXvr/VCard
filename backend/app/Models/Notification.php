<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    public $timestamps = false;

    protected $fillable = [
        'vcard',
        'message',
    ];

    public function vcard()
    {
        return $this->belongsTo(VCard::class, 'vcard', 'phone_number');
    }
}
