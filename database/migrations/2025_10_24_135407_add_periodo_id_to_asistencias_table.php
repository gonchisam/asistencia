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
            // 1. Añade la llave foránea a la tabla 'periodos'
            $table->foreignId('periodo_id')
                  ->nullable() // Permite nulos (buena práctica)
                  ->after('uid') // Coloca la columna después de 'uid'
                  ->constrained('periodos') // Enlaza a la tabla 'periodos'
                  ->onDelete('set null'); // Si se borra un periodo, la asistencia queda, pero sin enlace

            // 2. Opcional: El campo 'nombre' en asistencias es redundante
            // si ya tienes el 'uid'. Lo hacemos opcional (nullable).
            $table->string('nombre')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            // Revierte los cambios en orden inverso
            $table->string('nombre')->nullable(false)->change(); // Vuelve a hacerlo no-nullable
            
            $table->dropForeign(['periodo_id']); // Elimina la llave foránea
            $table->dropColumn('periodo_id');  // Elimina la columna
        });
    }
};