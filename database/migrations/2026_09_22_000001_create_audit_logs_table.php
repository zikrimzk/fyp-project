<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('request_id')->nullable()->index();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->string('category', 40)->index();
            $table->string('event', 120)->index();
            $table->string('outcome', 20)->default('success')->index();

            // Deliberately no foreign keys: audit evidence must survive account/data removal.
            $table->string('actor_type', 30)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_identifier')->nullable();
            $table->string('actor_name')->nullable();

            $table->string('subject_type', 100)->nullable();
            $table->string('subject_id', 100)->nullable();
            $table->string('subject_label')->nullable();
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->index(['actor_type', 'actor_id', 'occurred_at'], 'audit_actor_date_idx');
            $table->index(['category', 'occurred_at'], 'audit_category_date_idx');
            $table->index(['subject_type', 'subject_id'], 'audit_subject_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
