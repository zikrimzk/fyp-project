<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'sa_final_submission',
        'sa_status',
        'sa_signature_data',
        'student_id',
        'activity_id',
        'semester_id'
    ];
}
