<?php

// database/migrations/XXXX_XX_XX_XXXXXX_create_curso_horarios_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('restrict');
            $table->foreignId('aula_id')->constrained('aulas')->onDelete('restrict');
            $table->integer('dia_semana'); // 1 = Lunes, 2 = Martes... 7 = Domingo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_horarios');
    }
};