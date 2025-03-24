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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('staff_id')->unique();
            $table->string('staff_name');
            $table->string('staff_email')->unique();
            $table->string('staff_phoneno')->nullable();
            $table->string('staff_password');
            $table->integer('staff_role')->default(2)->comment('1- Committee 2- Lecturer 3- Timbalan Dekan Pendidikan 4-Dekan');
            $table->integer('staff_status')->default(1)->comment('1- Active 2- Inactive');
            $table->text('staff_photo')->nullable();
            $table->foreignId('department_id')->constrained('departments');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
