<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'af_title',
        'af_target',
        'af_status',
        'activity_id',

    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function fields()
    {
        return $this->hasMany(FormField::class, 'af_id');
    }
}
