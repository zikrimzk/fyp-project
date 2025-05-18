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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('student_matricno')->unique();
            $table->string('student_email')->unique();
            $table->string('student_password');
            $table->string('student_address')->nullable();
            $table->string('student_phoneno')->nullable();
            $table->string('student_gender');
            $table->integer('student_status')->default(1)->comment('1- Active 2- Inactive');
            $table->integer('student_role')->default(1)->comment('1- Normal Student');
            $table->text('student_photo')->nullable();
            $table->text('student_directory')->nullable();
            $table->string('student_titleOfResearch')->nullable();
            $table->integer('student_semcount')->default(0)->comment('Start with Semester 0');
            $table->integer('student_opcode')->default(1);
            $table->foreignId('semester_id')->constrained('semesters');
            $table->foreignId('programme_id')->constrained('programmes');
            $table->rememberToken();
            $table->timestamps();


            /*$table->string('student_bio')->nullable();*/
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
