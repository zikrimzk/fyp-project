<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        // GENERAL ATTRIBUTES
        'ff_category',
        'ff_label',
        'ff_order',

        // INPUT ATTRIBUTES
        'ff_component_type',
        'ff_placeholder',
        'ff_component_required',
        'ff_value_options',
        'ff_repeatable',

        // OUTPUT ATTRIBUTES
        'ff_table',
        'ff_datakey',
        'ff_extra_datakey',
        'ff_extra_condition',

        // TABLE ATTRIBUTES
        'ff_is_table',
        'ff_table_structure',
        'ff_table_data',

        // ADDITIONAL ATTRIBUTES
        'ff_append_text',

        // RELATIONSHIP
        'af_id',
    ];

    public function form()
    {
        return $this->belongsTo(ActivityForm::class, 'af_id');
    }
}
