<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Default_Category extends Model
{
    use HasFactory;

    protected $table = 'default_categories';
    public $timestamps = true;

    protected $fillable = [
        'type',
        'name',
        'custom_data',
        'custom_options',
    ];

    protected $dates = ['deleted_at'];
}
