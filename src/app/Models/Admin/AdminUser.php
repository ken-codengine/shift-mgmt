<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends \Illuminate\Foundation\Auth\User
{
    use HasFactory;

    protected $table = 'admin';

    protected $hidden = [
        'password'
    ];

    protected $fillable = [
        'name',
        'email',
        'password'
    ];
}
