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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('uid')->unique(); // RFID UID como string único

            // Campo para el estado del estudiante (0 inactivo, 1 activo - para eliminación lógica)
            $table->boolean('estado')->default(true)->comment('Estado del registro: 0 inactivo, 1 activo (eliminación lógica)');
            // Nuevo campo para la última acción registrada (ENTRADA/SALIDA). Puede ser nulo al inicio.
            $table->string('last_action')->nullable()->comment('Última acción de asistencia registrada (ENTRADA/SALIDA)');

            // Campos para auditoría: quién creó y quién actualizó
            $table->unsignedBigInteger('created_by')->comment('ID del usuario que creó este registro');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID del último usuario que modificó este registro');

            $table->timestamps(); // created_at y updated_at automáticos

            // Claves foráneas para los campos de auditoría, referenciando la tabla 'users'
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict'); // No permitir borrar usuario si creó registros
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null'); // Poner a nulo si el usuario que actualizó es borrado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};