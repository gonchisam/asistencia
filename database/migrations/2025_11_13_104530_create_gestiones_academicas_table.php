<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gestiones_academicas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Gestión 2025", "Semestre I-2025"
            $table->date('fecha_inicio'); // Ej: 2025-02-01
            $table->date('fecha_fin');    // Ej: 2025-11-30
            $table->boolean('actual')->default(false); // Para marcar cuál es la activa rápidamente
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestiones_academicas');
    }
};
