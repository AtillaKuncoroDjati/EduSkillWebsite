<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            // Skor risiko integritas gabungan 0-100 (pelanggaran + keystroke + paste + auto-submit).
            $table->float('integrity_risk_score')->nullable()->after('keystroke_flag');
        });
    }

    public function down(): void
    {
        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('integrity_risk_score');
        });
    }
};
