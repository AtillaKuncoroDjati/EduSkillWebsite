<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_quiz_integrity_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('user_quiz_attempt_id');
            $table->uuid('user_course_id');
            $table->uuid('content_id');
            $table->enum('event_type', ['tab_switch', 'window_blur', 'fullscreen_exit']);
            $table->unsignedInteger('violation_count')->default(0);
            $table->boolean('is_auto_submitted')->default(false);
            $table->timestamp('event_at')->useCurrent();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_quiz_attempt_id')->references('id')->on('user_quiz_attempts')->onDelete('cascade');
            $table->foreign('user_course_id')->references('id')->on('user_courses')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('contents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_quiz_integrity_events');
    }
};
