<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guard = "student";

    protected $fillable = [
        'student_name',
        'student_matricno',
        'student_email',
        'student_password',
        'student_address',
        'student_phoneno',
        'student_gender',
        'student_status',
        'student_role',
        'student_bio',
        'student_photo',
        'student_directory',
        'student_titleOfResearch',
        'student_semcount',
        'student_opcode',
        'semester_id',
        'programme_id',
    ];

    protected $hidden = [
        'student_password',
        'remember_token',
    ];

    protected $casts = [
        'student_password' => 'hashed',
    ];
}
