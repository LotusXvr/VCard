<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Default_Category extends Model
{
    use HasFactory;
    use SoftDeletes;

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
