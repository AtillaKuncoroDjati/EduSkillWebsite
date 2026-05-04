<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->boolean('is_ai_generated')->default(false)->after('max_violations');
            $table->unsignedTinyInteger('ai_question_count')->default(5)->after('is_ai_generated');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['is_ai_generated', 'ai_question_count']);
        });
    }
};
