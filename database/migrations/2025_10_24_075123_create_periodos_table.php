<?php

// database/migrations/XXXX_XX_XX_XXXXXX_create_periodos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Periodo 1", "Periodo 2"
            $table->time('hora_inicio'); // Ej: "18:30:00"
            $table->time('hora_fin'); // Ej: "19:40:00" (informativo)
            $table->integer('tolerancia_ingreso_minutos')->default(15); // ¡Tu regla de negocio!
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};