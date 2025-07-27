<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController; // Breeze crea este controlador
use Illuminate\Support\Facades\Auth; // Necesario para Auth::routes() o para Auth::user()
use App\Http\Controllers\StudentController;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación de Breeze
|--------------------------------------------------------------------------
|
| Laravel Breeze incluye sus propias rutas de autenticación
| que gestionan el login, registro, etc. Estas rutas deben ser accesibles
| para usuarios no autenticados.
|
*/
require __DIR__.'/auth.php'; // Esto incluirá las rutas de login, register, etc.

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Requieren Autenticación)
|--------------------------------------------------------------------------
|
| Todas las rutas dentro de este grupo 'middleware('auth')' requerirán
| que el usuario esté logueado para acceder a ellas.
|
*/
Route::middleware('auth')->group(function () {
    // Tu dashboard ahora requiere autenticación
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas de perfil (si las necesitas, Breeze las genera)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/students', [StudentController::class, 'store'])->name('students.store');

    // Puedes añadir más rutas protegidas aquí, por ejemplo para configuración o reportes
    // Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion');
});

// Opcional: Si quieres una página de bienvenida diferente para usuarios no autenticados
// Route::get('/welcome', function () {
//     return view('welcome');
// });

// Existing dashboard route
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('students', StudentController::class); // Resourceful routes for students
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion'); // Assuming you have this route
});

// Other existing routes (e.g., auth routes)
require __DIR__.'/auth.php';

// Optional: If you want a different welcome page for unauthenticated users
// Route::get('/welcome', function () {
//     return view('welcome');
// });

Route::patch('/students/{student}/restore', [App\Http\Controllers\StudentController::class, 'restore'])->name('students.restore');