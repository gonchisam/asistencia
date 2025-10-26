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

    // Rutas de perfil (gestionadas por Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestión de Estudiantes
    Route::resource('students', StudentController::class);
    Route::patch('/students/{student}/restore', [StudentController::class, 'restore'])->name('students.restore');

    // Ruta para Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');

    // --- NUEVA RUTA PARA DESVINCULAR DISPOSITIVO ---
    Route::put('students/{student}/unlink-device', [StudentController::class, 'unlinkDevice'])->name('students.unlinkDevice');
    
    // Rutas para Reportes
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/pdf', [ReportesController::class, 'generatePdf'])->name('reportes.pdf');
    Route::get('/reportes/excel', [ReportesController::class, 'generateExcel'])->name('reportes.excel');

    // Verifica si un UID ya existe (esta ruta sí necesita autenticación)
    Route::post('/students/check-uid', [StudentController::class, 'checkUid'])->name('students.check_uid');

    // Estadísticas
    Route::get('/estadisticas', [EstadisticasController::class, 'index'])->name('estadisticas.index');

    // ==========================================================
    // ==== 1. AÑADIR ESTA NUEVA RUTA (GET) ====
    Route::get('/profile/importar-docentes', [ProfileController::class, 'showImportForm'])
         ->name('profile.showImportForm');

    // ==== 2. ESTA ES LA RUTA QUE YA TENÍAS (POST) ====
    Route::post('/profile/import-docentes', [ProfileController::class, 'importDocentes'])
         ->name('profile.importDocentes');
    // ==========================================================

    // === INICIO: RUTA DE IMPORTACIÓN ===
    Route::post('/profile/import-docentes', [ProfileController::class, 'importDocentes'])
         ->name('profile.importDocentes');
    // === FIN: RUTA DE IMPORTACIÓN ===

    // --- NUEVAS RUTAS ADMINISTRATIVAS ---
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // --- Rutas CRUD básicas ---
        
        // Rutas para Aulas (admin/aulas)
        Route::resource('aulas', AulaController::class);
        
        // Rutas para Periodos (admin/periodos)
        Route::resource('periodos', PeriodoController::class);
        
        // --- [INICIO DE LA CORRECCIÓN] ---
        // Rutas para Materias (admin/materias)
        // Le decimos que excluya el método "show" que no usamos.
        Route::resource('materias', MateriaController::class)->except(['show']);
        // --- [FIN DE LA CORRECCIÓN] ---

        // --- NUEVAS RUTAS: IMPORTAR MATERIAS ---
        Route::get('materias/importar', [MateriaController::class, 'vistaImportar'])->name('materias.importar.vista');
        Route::post('materias/importar', [MateriaController::class, 'procesarImportar'])->name('materias.importar.procesar');

        // --- AÑADIR ESTA LÍNEA ---
        Route::delete('materias/destroy-all', [MateriaController::class, 'destroyAll'])->name('materias.destroyAll');

        // --- Rutas de Cursos (las más importantes) ---
        
        // Rutas estándar de Cursos (admin/cursos)
        Route::resource('cursos', CursoController::class);

        // --- NUEVAS RUTAS: IMPORTAR ESTUDIANTES ---
        Route::get('estudiantes/importar', [StudentController::class, 'vistaImportarEstudiantes'])->name('estudiantes.importar.vista');
        Route::post('estudiantes/importar', [StudentController::class, 'procesarImportarEstudiantes'])->name('estudiantes.importar.procesar');

        // --- NUEVAS RUTAS: ASIGNAR RFID POST-IMPORTACIÓN ---
        Route::get('estudiantes/asignar-uid', [StudentController::class, 'vistaAsignarUid'])->name('estudiantes.asignar-uid.vista');
        Route::post('estudiantes/asignar-uid', [StudentController::class, 'procesarAsignarUid'])->name('estudiantes.asignar-uid.procesar');
        // (Nota: Usaremos la ruta 'students.check_uid' que ya existe para el AJAX)
        
                
        // Rutas para GESTIONAR un curso específico (desde la vista 'cursos.show')
        
        // Añadir/Eliminar Horarios (Dia/Periodo/Aula) a un Curso
        Route::post('cursos/{curso}/horarios', [CursoController::class, 'storeHorario'])->name('cursos.horarios.store');
        Route::delete('cursos/horarios/{cursoHorario}', [CursoController::class, 'destroyHorario'])->name('cursos.horarios.destroy');

        // Inscribir/Eliminar Estudiantes a un Curso
        Route::post('cursos/{curso}/estudiantes', [CursoController::class, 'storeEstudiante'])->name('cursos.estudiantes.store');
        Route::delete('cursos/{curso}/estudiantes/{estudiante}', [CursoController::class, 'destroyEstudiante'])->name('cursos.estudiantes.destroy');

        // --- Rutas de Importación (Excel) ---
        
        // Vista para mostrar el formulario de subida
        Route::get('inscripciones/importar', [CursoController::class, 'vistaImportar'])->name('inscripciones.importar.vista');
        // Ruta que procesa el archivo Excel
        Route::post('inscripciones/importar', [CursoController::class, 'procesarImportacion'])->name('inscripciones.importar.procesar');
    });
});

// NUEVAS RUTAS PÚBLICAS PARA COMUNICACIÓN CON EL DISPOSITIVO ARDUINO
// Estas rutas no requieren autenticación
Route::post('/api/rfid-scan', [StudentController::class, 'receiveUid']);
Route::get('/api/get-uid', [StudentController::class, 'getTempUid']);