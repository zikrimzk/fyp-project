<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_name',
        'journal_scopus_isi',
        'student_id',
    ];
}
