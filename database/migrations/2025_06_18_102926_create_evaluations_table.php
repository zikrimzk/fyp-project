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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->integer('evaluation_status')->default(1);
            $table->dateTime('evaluation_date')->nullable();
            $table->json('evaluation_signature_data')->nullable()->comment('will store the respected signature_key: signature_data , signature_date:Date');
            $table->json('evaluation_meta_data')->nullable()->comment('will store other data in the form if needed');
            $table->string('evaluation_document')->nullable();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('staff_id')->constrained('staff');
            $table->foreignId('activity_id')->constrained('activities');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
