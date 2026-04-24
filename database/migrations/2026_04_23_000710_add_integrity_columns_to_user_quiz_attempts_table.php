<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->unsignedInteger('violation_count')->default(0)->after('is_passed');
            $table->boolean('is_auto_submitted')->default(false)->after('violation_count');
            $table->string('auto_submit_reason')->nullable()->after('is_auto_submitted');
        });
    }

    public function down(): void
    {
        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['violation_count', 'is_auto_submitted', 'auto_submit_reason']);
        });
    }
};
