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
        Schema::create('student_activities', function (Blueprint $table) {
            $table->id();
            $table->string('sa_final_submission');
            $table->integer('sa_status')->default(1)->comment('1- Pending 2- Approved 3- Rejected');
            $table->json('sa_signature_data')->comment('will store the respected signature_key: signature_data , signature_date:Date');
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('activity_id')->constrained('activities');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_activities');
    }
};
