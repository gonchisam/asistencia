<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\StudentController;
// --- ¡Ajusta esta línea! ---
use App\Http\Controllers\Api\MovilController; // Asegúrate que la ruta sea correcta

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- GRUPO DE RUTAS PARA LA APP MÓVIL ---
Route::prefix('movil')->group(function () {
    
    // Ruta pública para el login de la app
    Route::post('/login', [MovilController::class, 'login']);

    // Rutas protegidas que requieren autenticación (token Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        
        Route::get('/perfil', [MovilController::class, 'getPerfil']);
        Route::post('/logout', [MovilController::class, 'logout']);
        
        // Ruta para registrar la asistencia (POST)
        Route::post('/asistencia', [MovilController::class, 'registrarAsistencia']); 

        // Ruta de historial
        Route::get('/historial', [MovilController::class, 'getHistorial']);

        // --- ¡NUEVA RUTA AÑADIDA! ---
        // Ruta para que la app consulte si puede marcar (GET)
        Route::get('/estado-asistencia', [MovilController::class, 'getEstadoAsistencia']);
    });
});
// --- FIN DEL GRUPO ---


// --- Tus rutas existentes ---
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ruta para el RFID (Arduino)
Route::post('/asistencia', [AsistenciaController::class, 'store']);

// Ruta para sincronización en lote (Offline)
Route::post('/asistencia/batch', [AsistenciaController::class, 'storeBatch']);

// Rutas para estudiantes (probablemente para el admin web)
Route::post('/students', [StudentController::class, 'store']);
Route::get('/students-list', [StudentController::class, 'getStudentsList']);