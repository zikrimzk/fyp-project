<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected  $fillable =[
        'fac_name',
        'fac_code',
        'fac_logo',
        'fac_status'
    ];
}
