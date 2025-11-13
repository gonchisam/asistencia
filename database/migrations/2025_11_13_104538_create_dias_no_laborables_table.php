<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dias_no_laborables', function (Blueprint $table) {
            $table->id();
            $table->date('fecha'); // La fecha específica (ej: 2025-09-14)
            
            // Tipo de evento para colorearlo diferente en el calendario
            // 'FERIADO': Fijos (Navidad, 14 Sept)
            // 'VACACION': Rangos planificados (Invierno)
            // 'EMERGENCIA': Bloqueos, paros, desastres (Intempestivos)
            // 'TOLERANCIA': Hay clases pero se perdona retraso
            $table->enum('tipo', ['FERIADO', 'VACACION', 'EMERGENCIA', 'TOLERANCIA'])->default('FERIADO');
            
            $table->string('descripcion')->nullable(); // Ej: "Bloqueo en Av. Blanco Galindo"
            
            // Si es true, el sistema ignorará el AÑO de la fecha al validar.
            // Útil para el 14 de Septiembre, 25 de Diciembre, etc.
            $table->boolean('recurrente')->default(false); 
            
            // Opcional: Si quieres vincular vacaciones específicas a una gestión
            $table->foreignId('gestion_id')->nullable()->constrained('gestiones_academicas')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dias_no_laborables');
    }
};