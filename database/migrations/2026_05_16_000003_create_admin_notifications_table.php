<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 30);              // essay_submitted | integrity_violation
            $table->string('title');
            $table->string('message', 500);
            $table->string('link')->nullable();      // URL tujuan saat notifikasi diklik
            $table->uuid('related_id')->nullable();  // id attempt terkait
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['is_read', 'updated_at']);
            $table->index(['type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
