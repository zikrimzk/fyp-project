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
        Schema::create('submission_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('sr_comment');
            $table->string('sr_date');
            $table->foreignId('staff_id')->constrained('staff');
            $table->foreignId('student_activity_id')->constrained('student_activities');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_reviews');
    }
};
