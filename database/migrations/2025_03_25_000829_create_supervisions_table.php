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
        Schema::create('supervisions', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('staff_id')->constrained('staff');
            $table->integer('supervision_role')->comment('1- Main Supervisor 2- Co-Supervisor');
            $table->json('supervision_access')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisions');
    }
};
