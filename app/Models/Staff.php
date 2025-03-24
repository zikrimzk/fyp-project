<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $guard = "staff";

    protected $fillable = [
        'staff_id',
        'staff_name',
        'staff_email',
        'staff_phoneno',
        'staff_password',
        'staff_role',
        'staff_status',
        'staff_photo',
        'department_id'
    ];

    protected $hidden = [
        'staff_password',
        'remember_token',
    ];

    protected $casts = [
        'staff_password' => 'hashed',
    ];
}
