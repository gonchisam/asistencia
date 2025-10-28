<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // --- INICIO DE RUTAS PARA ESP32 ---
        // Añade estas líneas.
        // Esto le dice a Laravel que no pida un token CSRF
        // para estas rutas, solucionando el error 302 y 419.
        'api/asistencia',
        'api/asistencia/batch',
        'api/students-list',
        'api/rfid-scan',
        'api/get-uid',
        // --- FIN DE RUTAS PARA ESP32 ---
    ];
}