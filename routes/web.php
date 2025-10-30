<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\CursoController;

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

    Route::get('/dashboard/tabla', [DashboardController::class, 'fetchAsistenciaTabla'])->name('dashboard.tabla');

    // Rutas de perfil (gestionadas por Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Importar Docentes
    Route::get('/profile/importar-docentes', [ProfileController::class, 'showImportForm'])->name('profile.showImportForm');
    Route::post('/profile/import-docentes', [ProfileController::class, 'importDocentes'])->name('profile.importDocentes');

    // Gestión de Estudiantes
    Route::resource('students', StudentController::class);
    Route::patch('/students/{student}/restore', [StudentController::class, 'restore'])->name('students.restore');
    Route::put('students/{student}/unlink-device', [StudentController::class, 'unlinkDevice'])->name('students.unlinkDevice');
    Route::post('/students/check-uid', [StudentController::class, 'checkUid'])->name('students.check_uid');

    // Ruta para Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');

    // Rutas para Reportes
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/pdf', [ReportesController::class, 'generatePdf'])->name('reportes.pdf');
    Route::get('/reportes/excel', [ReportesController::class, 'generateExcel'])->name('reportes.excel');

    // Estadísticas
    Route::get('/estadisticas', [EstadisticasController::class, 'index'])->name('estadisticas.index');

    // --- RUTAS ADMINISTRATIVAS ---
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('horarios/formulario', [App\Http\Controllers\HorariosPdfController::class, 'formulario'])
            ->name('horarios.formulario');
        Route::post('horarios/generar-pdf', [App\Http\Controllers\HorariosPdfController::class, 'generarPdf'])
        ->name('horarios.generar-pdf');
        // === AULAS ===
        Route::resource('aulas', AulaController::class);
        
        // === PERIODOS ===
        Route::resource('periodos', PeriodoController::class);
        
        // === MATERIAS ===
        Route::get('materias/importar', [MateriaController::class, 'vistaImportar'])->name('materias.importar.vista');
        Route::post('materias/importar', [MateriaController::class, 'procesarImportar'])->name('materias.importar.procesar');
        Route::delete('materias/destroy-all', [MateriaController::class, 'destroyAll'])->name('materias.destroyAll');
        Route::resource('materias', MateriaController::class)->except(['show']);

        // === ESTUDIANTES (Importación y Asignación de UID) ===
        Route::get('estudiantes/importar', [StudentController::class, 'vistaImportarEstudiantes'])->name('estudiantes.importar.vista');
        Route::post('estudiantes/importar', [StudentController::class, 'procesarImportarEstudiantes'])->name('estudiantes.importar.procesar');
        Route::get('estudiantes/asignar-uid', [StudentController::class, 'vistaAsignarUid'])->name('estudiantes.asignar-uid.vista');
        Route::post('estudiantes/asignar-uid', [StudentController::class, 'procesarAsignarUid'])->name('estudiantes.asignar-uid.procesar');

        // === CURSOS ===
        // ⚠️ IMPORTANTE: Las rutas específicas deben ir ANTES del Route::resource
        
        // Importación de Cursos Completos
        Route::get('cursos/importar-completos', [CursoController::class, 'vistaImportarCompletos'])->name('cursos.importar-completos.vista');
        Route::post('cursos/importar-completos', [CursoController::class, 'procesarImportarCompletos'])->name('cursos.importar-completos.procesar');
        
        // Importación de Inscripciones
        Route::get('inscripciones/importar', [CursoController::class, 'vistaImportar'])->name('inscripciones.importar.vista');
        Route::post('inscripciones/importar', [CursoController::class, 'procesarImportacion'])->name('inscripciones.importar.procesar');

        // Rutas CRUD estándar de Cursos
        Route::resource('cursos', CursoController::class);

        // Gestión de Horarios de un Curso
        Route::post('cursos/{curso}/horarios', [CursoController::class, 'storeHorario'])->name('cursos.horarios.store');
        Route::delete('cursos/horarios/{cursoHorario}', [CursoController::class, 'destroyHorario'])->name('cursos.horarios.destroy');

        // Gestión de Estudiantes de un Curso
        Route::post('cursos/{curso}/estudiantes', [CursoController::class, 'storeEstudiante'])->name('cursos.estudiantes.store');
        Route::delete('cursos/{curso}/estudiantes/{estudiante}', [CursoController::class, 'destroyEstudiante'])->name('cursos.estudiantes.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas Públicas (API para Arduino/RFID)
|--------------------------------------------------------------------------
*/
Route::post('/api/rfid-scan', [StudentController::class, 'receiveUid']);
Route::get('/api/get-uid', [StudentController::class, 'getTempUid']);