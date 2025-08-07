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
        Schema::create('procedures', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained('activities');
            $table->foreignId('programme_id')->constrained('programmes');;
            $table->integer('act_seq');
            $table->integer('timeline_sem');
            $table->integer('timeline_week');
            $table->integer('init_status')->default(2)->comment('1-Open Always 2-Locked');
            $table->integer('is_repeatable')->default(0)->comment('1-Yes 0-No');
            $table->integer('is_haveJournalPublication')->default(0)->comment('1-Yes 0-No');
            $table->integer('is_haveEva')->default(0)->comment('1-Yes 0-No');
            $table->text('material')->nullable();
            $table->timestamps();
        });


         Schema::create('procedures', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained('activities');
            $table->foreignId('programme_id')->constrained('programmes');
            $table->integer('activity_type')->comment('1-Submission 2-Record-based');
            $table->integer('act_seq');
            $table->integer('timeline_sem');
            $table->integer('timeline_week');
            $table->integer('init_status')->default(2)->comment('1-Open Always 2-Locked');
            $table->integer('is_repeatable')->default(0)->comment('1-Yes 0-No');
            $table->integer('is_haveJournalPublication')->default(0)->comment('1-Yes 0-No');
            $table->integer('is_haveEva')->default(0)->comment('1-Yes 0-No');
            $table->integer('evaluation_mode')->nullable()->comment('1-Report 2-Report + Approval');
            $table->text('material')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};
