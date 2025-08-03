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
        Schema::create('activity_corrections', function (Blueprint $table) {
            $table->id();
            $table->string('ac_final_submission');
            $table->integer('ac_status')->default(1)->comment('1- Pending 2- Approved SV 3- Approved Examiner/Panel 4- Approved Committee 5- Rejected');
            $table->json('ac_signature_data')->comment('will store the respected signature_key: signature_data , signature_date:Date');
            $table->dateTime('ac_startdate')->nullable();
            $table->dateTime('ac_duedate')->nullable();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('activity_id')->constrained('activities');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_corrections');
    }
};
