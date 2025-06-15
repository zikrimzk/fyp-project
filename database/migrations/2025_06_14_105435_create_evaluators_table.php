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
        Schema::create('evaluators', function (Blueprint $table) {
            $table->id();
            $table->integer('eva_role')->default(1)->comment('1- examiner | 2- panel | 3 - chairmain');
            $table->integer('eva_status')->default(1)->comment('1- supervisor nomination | 2- committee nomination | 3 - approved');
            $table->json('eva_meta')->nullable()->comment('Field metadata and original input');
            $table->foreignId('staff_id')->constrained('staff');
            $table->foreignId('nom_id')->constrained('nominations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluators');
    }
};
