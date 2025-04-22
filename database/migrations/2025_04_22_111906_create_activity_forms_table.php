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
        Schema::create('activity_forms', function (Blueprint $table) {
            $table->id();
            $table->string('af_title');
            $table->integer('af_target')->comment('1- Submission 2-Nomination 3-Evaluation'); 
            $table->integer('af_status')->default(1)->comment('1- Active 2- Inactive');
            $table->foreignId('activity_id')->constrained('activities');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_forms');
    }
};
