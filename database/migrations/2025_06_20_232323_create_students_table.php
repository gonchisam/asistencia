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
            $table->string('uid')->unique(); // RFID UID como string único
            $table->string('nombre');
            $table->string('primer_apellido');
            $table->string('segundo_apellido')->nullable();
            $table->string('ci')->unique();
            $table->date('fecha_nacimiento');
            $table->enum('carrera', ['Contabilidad', 'Secretariado', 'Mercadotecnia', 'Sistemas']);
            $table->enum('año', ['Primer Año', 'Segundo Año', 'Tercer Año']);
            $table->enum('sexo', ['MASCULINO', 'FEMENINO']);
            $table->string('celular')->nullable();
            $table->string('correo');
            
            // Campo para el estado del estudiante (0 inactivo, 1 activo - para eliminación lógica)
            $table->boolean('estado')->default(true)->comment('Estado del registro: 0 inactivo, 1 activo (eliminación lógica)');
            $table->string('device_id') // Tipo string para guardar el ID
                  ->nullable()         // Permite que esté vacío al principio
                  ->unique();           // Asegura que cada device_id sea único en la tabla
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
        Schema::table('students', function (Blueprint $table) { // Nombre correcto de tu tabla
            // Eliminamos la columna si revertimos
            $table->dropUnique(['device_id']); // Primero quita el índice único
            $table->dropColumn('device_id');
        });
    }
};