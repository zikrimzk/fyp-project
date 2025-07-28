<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_document',
        'submission_date',
        'submission_duedate',
        'submission_status',
        'student_id',
        'document_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
