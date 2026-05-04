<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kursus_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('kursus_id')->constrained('kursuses')->cascadeOnDelete();
            $table->foreignUuid('prerequisite_kursus_id')->references('id')->on('kursuses')->cascadeOnDelete();
            $table->unique(['kursus_id', 'prerequisite_kursus_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kursus_prerequisites');
    }
};
