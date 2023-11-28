<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens as PassportHasApiTokens;

class User extends Authenticatable
{
    use PassportHasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $timestamps = true;

    protected $table = 'view_auth_users';

    public function findForPassport(string $username): User
    {
        return $this->where('username', $username)->first();
    }

    protected $fillable = [
        'name',
        'email',
        'username',
        'photo_url',
        'password',
        'custom_data',
        'custom_options',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    protected $dates = ['created_at', 'updated_at', 'email_verified_at'];
}
