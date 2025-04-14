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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->text('submission_document');
            $table->dateTime('submission_date')->nullable();
            $table->dateTime('submission_duedate')->nullable();
            $table->integer('submission_status')->default(2)->comment('1- Open [No Attempt] 2- Locked [Closed Submission] 3- Submitted 4- Overdue 5- Deleted');
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('document_id')->constrained('documents');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
