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
            $table->string('uid');
            $table->string('nombre')->nullable();
            $table->string('accion')->comment('ENTRADA o SALIDA');
            $table->string('modo')->comment('Manual, QR o RFID');
            $table->timestamp('fecha_hora');
            $table->timestamps();

            $table->foreign('uid')
                  ->references('uid')
                  ->on('students')
                  ->onDelete('cascade')
                  ->onUpdate('cascade'); // <-- ¡Esta es la línea clave!
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