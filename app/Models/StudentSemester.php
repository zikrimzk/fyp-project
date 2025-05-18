<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSemester extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'semester_id',
        'ss_status',
    ];
}
