<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $fillable = [
        'vcard',
        'type',
        'value',
        'old_balance',
        'new_balane',
        'payment_type',
        'payment_reference',
        'pair_transaction',
        'pair_vcard',
        'category_id',
        'description',
        'custom_data',
        'custom_options',
    ];

    protected $dates = ['deleted_at', 'date', 'date_time', 'created_at', 'updated_at'];

    public function vcards()
    {
        return $this->belongsTo(VCard::class, 'vcard', 'phone_number'); // 'vcard' is the foreign key in the transactions table
    }

    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function pairVcards()
    {
        return $this->belongsTo(VCard::class, 'pair_vcard');
    }

    public function pairTransaction()
    {
        return $this->hasOne(Transaction::class, 'pair_transaction');
    }
}
