<?php

// database/migrations/XXXX_XX_XX_XXXXXX_create_materias_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Contabilidad Básica"
            $table->string('carrera'); // Ej: "Contabilidad", "Sistemas"
            $table->string('ano_cursado'); // Ej: "Primer Año", "Segundo Año"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};