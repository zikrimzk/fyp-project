<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'staff';
    protected $guard = 'staff';

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

    public function getAuthIdentifierName()
    {
        return 'staff_email';
    }

    public function departments()
    {
        return $this->belongsTo(Department::class,'department_id');
    }
}
