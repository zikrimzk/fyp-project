<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionReview extends Model
{
    use HasFactory;

     protected $fillable = [
        'sr_comment',
        'sr_date',
        'staff_id',
        'student_activity_id',
    ];
}
