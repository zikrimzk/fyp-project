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
        Schema::create('journal_publications', function (Blueprint $table) {
            $table->id();
            $table->string('journal_name');
            $table->integer('journal_scopus_isi')->default(0)->comment('1-Yes 0-No');
            $table->foreignId('student_id')->constrained('students');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_publications');
    }
};
