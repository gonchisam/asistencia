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
require __DIR__.'/auth.php'; // Incluye las rutas de autenticación de Laravel Breeze

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren Autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard (Asistencia) - Esta será tu vista principal de Asistencia
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']); // Opcional, si quieres /dashboard además de /

    // Rutas de perfil (gestionadas por Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestión de Estudiantes (ya existente)
    Route::resource('students', StudentController::class);
    Route::patch('/students/{student}/restore', [StudentController::class, 'restore'])->name('students.restore');

    // Ruta para Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');

    // Ruta para Reportes
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
});