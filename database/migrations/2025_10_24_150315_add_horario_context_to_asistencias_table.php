<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            
            // --- NUEVOS CAMPOS ---

            // ID del curso al que pertenece esta asistencia
            $table->unsignedBigInteger('curso_id')->nullable()->after('uid');
            // ID del periodo (bloque horario) en el que se marcó
            $table->unsignedBigInteger('periodo_id')->nullable()->after('curso_id');
            
            // Estado de la llegada (a_tiempo, tarde, falta)
            // Esto reemplaza la lógica automática que tenías
            $table->string('estado_llegada')->default('a_tiempo')->after('modo');
            
            // --- FIN NUEVOS CAMPOS ---

            // --- Llaves Foráneas (Opcional pero recomendado) ---
            // Asumiendo que `cursos` y `periodos` tienen `id`
             $table->foreign('curso_id')
                  ->references('id')
                  ->on('cursos')
                  ->onDelete('set null'); // Si se borra el curso, no borres la asistencia

             $table->foreign('periodo_id')
                  ->references('id')
                  ->on('periodos')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropForeign(['curso_id']);
            $table->dropForeign(['periodo_id']);
            $table->dropColumn(['curso_id', 'periodo_id', 'estado_llegada']);
        });
    }
};