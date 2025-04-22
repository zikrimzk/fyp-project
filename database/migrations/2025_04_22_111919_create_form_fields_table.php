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
            $table->string('ff_label');
            $table->string('ff_datakey')->comment('database attribute');
            $table->integer('ff_order')->default(1);
            $table->boolean('ff_isbold')->default(false);
            $table->boolean('ff_isheader')->default(false);
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
