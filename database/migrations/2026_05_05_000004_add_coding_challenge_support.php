<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('coding_language')->default('python')->after('grading_type');
            $table->text('starter_code')->nullable()->after('coding_language');
            $table->text('expected_output')->nullable()->after('starter_code');
        });

        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->text('submitted_code')->nullable()->after('admin_notes');
            $table->string('coding_language')->nullable()->after('submitted_code');
            $table->text('actual_output')->nullable()->after('coding_language');
            $table->integer('judge0_status_id')->nullable()->after('actual_output');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['coding_language', 'starter_code', 'expected_output']);
        });

        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['submitted_code', 'coding_language', 'actual_output', 'judge0_status_id']);
        });
    }
};
