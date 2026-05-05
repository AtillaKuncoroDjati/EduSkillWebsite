<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('quiz_type')->default('multiple_choice')->after('is_ai_generated');
        });

        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->json('essay_answers')->nullable()->after('ai_answers');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('quiz_type');
        });

        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('essay_answers');
        });
    }
};
