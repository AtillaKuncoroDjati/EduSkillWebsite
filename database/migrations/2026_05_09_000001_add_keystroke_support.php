<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keystroke_baselines', function (Blueprint $table) {
            $table->id();
            $table->char('user_id', 36)->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('device_type', 20)->default('desktop'); // desktop / mobile
            $table->float('mean_dwell')->nullable();   // ms — average key-hold duration
            $table->float('std_dwell')->nullable();    // ms — standard deviation
            $table->float('mean_flight')->nullable();  // ms — average inter-key gap
            $table->float('std_flight')->nullable();
            $table->float('mean_speed_cps')->nullable(); // chars per second
            $table->float('std_speed_cps')->nullable();
            $table->float('mean_error_rate')->nullable(); // 0.0–1.0
            $table->unsignedSmallInteger('sample_sessions')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'device_type']);
        });

        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->json('keystroke_data')->nullable()->after('admin_notes');
            $table->float('keystroke_anomaly_score')->nullable()->after('keystroke_data');
            $table->string('keystroke_flag', 20)->nullable()->after('keystroke_anomaly_score'); // normal/caution/suspect
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keystroke_baselines');
        Schema::table('user_quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['keystroke_data', 'keystroke_anomaly_score', 'keystroke_flag']);
        });
    }
};
