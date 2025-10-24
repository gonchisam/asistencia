<?php

// database/migrations/XXXX_XX_XX_XXXXXX_create_cursos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            // Esta es la llave foránea a la tabla 'materias'
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->string('paralelo'); // Ej: "A", "B", "Unico"
            $table->string('gestion'); // Ej: "2025", "1-2025"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};