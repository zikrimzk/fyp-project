<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervision extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'staff_id',
        'supervision_role',
        'supervision_access',
    ];


}
