<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->boolean('integrity_mode_enabled')->default(false)->after('content');
            $table->boolean('require_fullscreen')->default(false)->after('integrity_mode_enabled');
            $table->unsignedInteger('max_violations')->default(3)->after('require_fullscreen');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['integrity_mode_enabled', 'require_fullscreen', 'max_violations']);
        });
    }
};
