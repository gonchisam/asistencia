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
        Schema::table('students', function (Blueprint $table) {
            // ¡AQUÍ ESTÁ EL CAMBIO!
            // Solo modificamos la columna para que sea NULABLE.
            // No volvemos a añadir ->unique() porque ya existe.
            $table->string('uid')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // El reverso es volver a ponerla como NO NULABLE
            // (Nota: esto fallará si ya tienes estudiantes con UID nulo)
            $table->string('uid')->nullable(false)->change();
        });
    }
};