<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'doc_name',
        'isShowDoc',
        'isRequired',
        'doc_status',
        'activity_id'
    ];
}
