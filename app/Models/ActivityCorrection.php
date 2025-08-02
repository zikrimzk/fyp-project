<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityCorrection extends Model
{
    use HasFactory;

     protected $fillable = [
        'ac_final_document',
        'ac_status',
        'ac_signature_data',
        'ac_date',
        'student_id',
        'activity_id',
        'semester_id',
    ];
}
