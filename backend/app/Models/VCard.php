<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class VCard extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'vcards';
    public $timestamps = true;
    protected $primaryKey = 'phone_number';

    protected $fillable = [
        'phone_number',
        'name',
        'email',
        'photo_url',
        'password',
        'confirmation_code',
        'blocked',
        'balance',
        'savings',
        'spins',
        'max_debit',
        'custom_options',
        'custom_data',
    ];

    protected $casts = [
    ];

    protected $dates = ['created_at', 'deleted_at', 'updated_at'];

    public function categories()
    {
        return $this->hasMany(Category::class, 'vcard');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'vcard', 'phone_number');
    }

    public function transactionsPairVcard()
    {
        return $this->hasMany(Category::class, 'pair_vcard');
    }

}

