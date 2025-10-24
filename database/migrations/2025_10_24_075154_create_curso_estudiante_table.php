<?php

// database/migrations/XXXX_XX_XX_XXXXXX_create_curso_estudiante_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_estudiante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            
            // Asumo que tu tabla de estudiantes se llama 'students' (basado en los archivos de tu proyecto)
            $table->foreignId('estudiante_id')->constrained('students')->onDelete('cascade');

            $table->timestamps();

            // Para evitar que un estudiante se inscriba dos veces en el mismo curso
            $table->unique(['curso_id', 'estudiante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_estudiante');
    }
};