<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('grading_type')->default('ai')->after('quiz_type'); // 'ai' or 'manual'
        });

        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->string('grading_status')->nullable()->after('essay_answers'); // 'pending_review', 'graded'
            $table->text('admin_notes')->nullable()->after('grading_status');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('grading_type');
        });

        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['grading_status', 'admin_notes']);
        });
    }
};
