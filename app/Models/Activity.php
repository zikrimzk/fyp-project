<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'act_name'
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
