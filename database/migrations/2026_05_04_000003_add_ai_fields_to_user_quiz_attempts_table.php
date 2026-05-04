<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->json('generated_questions')->nullable()->after('auto_submit_reason');
            $table->json('ai_answers')->nullable()->after('generated_questions');
        });
    }

    public function down(): void
    {
        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['generated_questions', 'ai_answers']);
        });
    }
};
