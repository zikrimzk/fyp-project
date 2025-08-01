<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            // GENERAL ATTRIBUTES
            $table->integer('ff_category')->comment('1 - input, 2 - output, 3 - Section, 4 - Text, 5 - Table, 6 - Signature');
            $table->text('ff_label')->nullable()->comment('Label/title/description depending on category');
            $table->integer('ff_order')->default(1)->comment('Display order in the form');

            // INPUT ATTRIBUTES
            $table->string('ff_component_type')->nullable()->comment('text, textarea, select, checkbox, radio, date, time, datetime, file');
            $table->string('ff_placeholder')->nullable()->comment('Placeholder text for input fields');
            $table->integer('ff_component_required')->default(2)->comment('1 - required, 2 - optional');
            $table->integer('ff_component_required_role')->nullable()->default(0)->comment('0- All, 1 - Supervisors, 2 - Admistrators');
            $table->json('ff_value_options')->nullable()->comment('For select, checkbox, and radio (JSON array)');

            // OUTPUT ATTRIBUTES
            $table->string('ff_table')->nullable()->comment('Related table for data output');
            $table->string('ff_datakey')->nullable()->comment('Main data key from database');
            $table->string('ff_extra_datakey')->nullable()->comment('Optional extra data key');
            $table->string('ff_extra_condition')->nullable()->comment('Condition to filter data');

            //TABLE ATTRIBUTES
            $table->boolean('ff_is_table')->default(false)->comment('Is this field a dynamic table?');
            $table->json('ff_table_structure')->nullable()->comment('JSON defining table columns, headers, types');
            $table->json('ff_table_data')->nullable()->comment('Optional default data in the table (rows)');

            // SIGNATURE ATTRIBUTES
            $table->integer('ff_signature_role')->nullable()->comment('Related table for data output');
            $table->string('ff_signature_key')->nullable()->comment('Key to map to the actual signature image, e.g., "supervisor_signature"');
            $table->string('ff_signature_date_key')->nullable()->comment('Key to map to signature date, e.g., "supervisor_signature_date"');

            // ADDITIONAL ATTRIBUTES
            $table->text('ff_append_text')->nullable()->comment('Text to append after field label');

            // RELATIONSHIP
            $table->foreignId('af_id')->constrained('activity_forms');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
