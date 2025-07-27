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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->string('uid'); // UID del estudiante
            $table->string('nombre'); // Nombre del estudiante (para historial/reportes directos)
            $table->string('accion'); // ENTRADA o SALIDA
            $table->string('modo');   // WIFI o SD
            $table->timestamp('fecha_hora');

            $table->timestamps(); // created_at y updated_at

            // Relación a estudiantes (por UID)
            $table->foreign('uid')->references('uid')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};