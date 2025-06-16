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
        Schema::create('nominations', function (Blueprint $table) {
            $table->id();
            $table->integer('nom_status')->default(1)->comment('1- Pending | 2- Nominated => SV | 3 - Nominated =>Committee | 4- Approved');
            $table->dateTime('nom_date')->nullable();
            $table->json('nom_signature_data')->nullable()->comment('will store the respected signature_key: signature_data , signature_date:Date');
            $table->string('nom_document')->nullable();
            $table->json('nom_extra_data')->nullable()->comment('will store other data in the form if needed');
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
        Schema::dropIfExists('nominations');
    }
};
