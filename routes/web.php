<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ReportesController;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación de Breeze
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren Autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard (Asistencia)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Rutas de perfil (gestionadas por Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestión de Estudiantes
    Route::resource('students', StudentController::class);
    Route::patch('/students/{student}/restore', [StudentController::class, 'restore'])->name('students.restore');

    // Ruta para Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');

    // Rutas para Reportes
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/pdf', [ReportesController::class, 'generatePdf'])->name('reportes.pdf');
    Route::get('/reportes/excel', [ReportesController::class, 'generateExcel'])->name('reportes.excel');

    // En routes/web.php

    // Dentro del grupo Route::middleware('auth')->group(...)

    Route::post('/students/check-uid', [StudentController::class, 'checkUid'])->name('students.check_uid');
}); 