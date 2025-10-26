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
        Schema::table('cursos', function (Blueprint $table) {
            // Añadimos la columna docente_id (puede ser nullable si algunos cursos no tienen docente asignado aún)
            $table->foreignId('docente_id')
                  ->nullable()
                  ->after('gestion')
                  ->constrained('users') // Se relaciona con la tabla 'users'
                  ->onDelete('set null'); // Si se elimina el docente, el campo queda en NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropForeign(['docente_id']);
            $table->dropColumn('docente_id');
        });
    }
};