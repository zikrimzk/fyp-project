<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'activity_id',
        'programme_id',
        'activity_type',
        'act_seq',
        'timeline_sem',
        'timeline_week',
        'init_status',
        'is_haveEva',
        'evaluation_mode',
        'is_repeatable',
        'is_haveJournalPublication',
        'material',
    ];
}
