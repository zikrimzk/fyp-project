<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'ff_label',
        'ff_datakey',
        'ff_order',
        'ff_isbold',
        'ff_isheader',
        'af_id',
    ];

    public function form()
    {
        return $this->belongsTo(ActivityForm::class, 'af_id');
    }
}
