<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'users';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'password',
        'custom_option',
        'custom_data',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'custom_option' => 'array',
        'custom_data' => 'array',
        'password' => 'hashed',
        'confirmation_code' => 'hashed',
    ];

    protected $dates = ['created_at', 'updated_at', 'email_verified_at'];
}
