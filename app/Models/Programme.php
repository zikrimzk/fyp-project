<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    use HasFactory;

    protected $fillable = [
        'prog_code',
        'prog_name',
        'prog_mode',
        'prog_status',
        'fac_id',
    ];
}
