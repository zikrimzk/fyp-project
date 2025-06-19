<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

     protected $fillable = [
        'evaluation_status',
        'evaluation_date',
        'evaluation_signature_data',
        'evaluation_meta_data',
        'evaluation_document',
        'student_id',
        'staff_id',
        'activity_id'
    ];
}
