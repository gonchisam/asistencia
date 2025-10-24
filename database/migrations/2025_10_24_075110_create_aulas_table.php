<?php

// database/migrations/XXXX_XX_XX_XXXXXX_create_aulas_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Aula 101", "Laboratorio de Cómputo"
            $table->string('codigo')->nullable(); // Ej: "A-101"
            $table->string('ubicacion')->nullable(); // Ej: "Piso 1, Bloque A"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};