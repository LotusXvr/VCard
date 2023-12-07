<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'categories';
    public $timestamps = false;

    protected $fillable = [
        'vcard',
        'type',
        'name',
        'custom_data',
        'custom_options',
    ];

    protected $dates = ['deleted_at'];

    public function vcards (){
        return $this->belongsTo(VCard::class, 'phone_number');
    }

    public function transacions (){
        return $this->hasMany(Transaction::class, 'category_id');
    }
}
