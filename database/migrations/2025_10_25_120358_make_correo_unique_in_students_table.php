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
            // Hacemos que la columna correo sea NULABLE y ÚNICA
            // (Asumimos que puede ser nulo si cambiaste la lógica, si no, solo ->unique()->change())
            $table->string('correo')->nullable()->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['correo']);
            $table->string('correo')->nullable()->change();
        });
    }
};
