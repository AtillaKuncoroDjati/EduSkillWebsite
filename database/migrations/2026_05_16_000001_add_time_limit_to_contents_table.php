<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // Batas waktu pengerjaan kuis dalam menit. NULL = tanpa batas waktu.
            $table->unsignedInteger('time_limit_minutes')->nullable()->after('max_violations');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('time_limit_minutes');
        });
    }
};
