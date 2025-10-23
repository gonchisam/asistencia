<?php

use Illuminateate\Http\Request;
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
        
        // Apunta al método correcto que creaste (registrarAsistencia)
        Route::post('/asistencia', [MovilController::class, 'registrarAsistencia']); 

        // ¡La nueva ruta de historial!
        Route::get('/historial', [MovilController::class, 'getHistorial']);
    });
});
// --- FIN DEL GRUPO ---


// --- Tus rutas existentes ---
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/asistencia', [AsistenciaController::class, 'store']);
Route::post('/asistencia/batch', [AsistenciaController::class, 'storeBatch']);
Route::post('/students', [StudentController::class, 'store']);
Route::get('/students-list', [StudentController::class, 'getStudentsList']);