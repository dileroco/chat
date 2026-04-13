<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'username',
        'password',
        'profile_picture',
        'created_at', 
    ];

    public $timestamps = false; 
}