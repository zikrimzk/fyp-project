<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_status',
        'nom_date',
        'nom_signature_data',
        'nom_document',
        'nom_extra_data',
        'student_id',
        'activity_id',
        'semester_id',
    ];
}
