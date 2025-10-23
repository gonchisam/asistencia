<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\StudentController;
// --- AÑADIR LOS NUEVOS CONTROLADORES MÓVILES ---
use App\Http\Controllers\Api\AuthMovilController;
use App\Http\Controllers\Api\AsistenciaMovilController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- RUTAS PÚBLICAS (EXISTENTES PARA ARDUINO) ---
Route::post('/asistencia', [AsistenciaController::class, 'store']);
Route::post('/asistencia/batch', [AsistenciaController::class, 'storeBatch']);
Route::get('/students-list', [StudentController::class, 'getStudentsList']);


// --- NUEVAS RUTAS API MÓVIL (ESTUDIANTES) ---

// Ruta PÚBLICA para que el estudiante inicie sesión
Route::post('/movil/login', [AuthMovilController::class, 'login']);

// Rutas PROTEGIDAS: Requieren un token de estudiante válido
// Usamos el guard 'estudiantes_api' que definimos en config/auth.php
Route::middleware('auth:estudiantes_api')->group(function () {
    
    // Obtener el perfil del estudiante logueado
    Route::get('/movil/perfil', [AuthMovilController::class, 'perfil']);

    // Cerrar la sesión del estudiante
    Route::post('/movil/logout', [AuthMovilController::class, 'logout']);

    // Registrar la asistencia por GPS
    Route::post('/movil/asistencia', [AsistenciaMovilController::class, 'registrar']);
});