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
        Schema::table('students', function (Blueprint $table) { // Asegúrate que 'students' sea el nombre correcto de tu tabla
            // Añadimos la columna device_id
            $table->string('device_id') // Tipo string para guardar el ID
                  ->nullable()         // Permite que esté vacío al principio
                  ->unique()           // Asegura que cada device_id sea único en la tabla
                  ->after('año');      // Opcional: Coloca la columna después de 'año' (o la columna que prefieras)
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