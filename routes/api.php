<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Api\MovilController;
use App\Http\Controllers\Api\StudentLoginController;

/*
|--------------------------------------------------------------------------
| API Routes - Sistema de Asistencia RFID + App Móvil
|--------------------------------------------------------------------------
| Todas estas rutas tienen el prefijo /api/ automáticamente
| y NO requieren autenticación web (sesiones de navegador).
*/

// ============================================
// 📡 RUTAS PÚBLICAS PARA ESP32/RFID
// ============================================
// ⚠️ CRÍTICO: Estas rutas DEBEN estar SIN middleware de autenticación
// porque el Arduino/ESP32 no maneja sesiones ni tokens Sanctum

// --- NUEVO ENDPOINT SEMÁFORO (para App y Arduino online) ---
Route::get('/asistencia/verificar', [AsistenciaController::class, 'verificarEstadoAsistencia']);

// Endpoint principal para registrar asistencia desde RFID
Route::post('/asistencia', [AsistenciaController::class, 'store']);

// Endpoint para sincronización de registros offline (modo batch)
Route::post('/asistencia/batch', [AsistenciaController::class, 'storeBatch']);

// Endpoint para que el Arduino descargue la lista de estudiantes
Route::get('/students-list', [StudentController::class, 'getStudentsList']);

// Endpoint para registrar UIDs desconocidos (tarjetas no asignadas)
Route::post('/rfid-scan', [StudentController::class, 'receiveUid']);

// Endpoint para obtener UID temporal (usado en asignación de tarjetas)
Route::get('/get-uid', [StudentController::class, 'getTempUid']);

// ============================================
// 📱 RUTAS PARA LA APP MÓVIL
// ============================================
Route::prefix('movil')->name('movil.')->group(function () {
    
    // Ruta pública para login (NO requiere autenticación previa)
    Route::post('/login', [MovilController::class, 'login'])->name('login');
    
    // Rutas protegidas con Sanctum (requieren token válido)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Perfil del estudiante autenticado
        Route::get('/perfil', [MovilController::class, 'getPerfil'])->name('perfil');
        
        // Cerrar sesión (revoca el token actual)
        Route::post('/logout', [MovilController::class, 'logout'])->name('logout');
        
        // Registrar asistencia desde la app móvil
        Route::post('/asistencia', [MovilController::class, 'registrarAsistencia'])->name('asistencia');
        
        // Obtener historial de asistencias
        Route::get('/historial', [MovilController::class, 'getHistorial'])->name('historial');
        
        // Consultar si puede marcar asistencia ahora (estado del botón)
        Route::get('/estado-asistencia', [MovilController::class, 'getEstadoAsistencia'])->name('estado');
    });
});

// ============================================
// 🔐 RUTA PROTEGIDA DE EJEMPLO (Sanctum)
// ============================================
// Endpoint para obtener datos del usuario autenticado
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'user' => $request->user()
    ]);
});

// ============================================
// 🔄 RUTAS ADICIONALES (para compatibilidad)
// ============================================
// Rutas de registro existentes (mantenemos para compatibilidad)
Route::post('/asistencia/rfid', [AsistenciaController::class, 'storeRfid']);
Route::get('/estudiantes/dispositivo/{device_id}', [AsistenciaController::class, 'getEstudiantesPorDispositivo']);
Route::post('/asistencia/offline-sync', [AsistenciaController::class, 'syncOfflineAsistencias']);

// Ruta protegida para marcar asistencia desde app móvil (alternativa)
Route::middleware('auth:sanctum')->post('/movil/marcar-asistencia', [MovilController::class, 'marcarAsistencia']);