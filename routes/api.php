<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\StudentController; // Make sure this is imported

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Example route for authenticated user info (optional, remove if not needed)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes for Asistencia (Attendance)
Route::post('/asistencia', [AsistenciaController::class, 'store']);
Route::post('/asistencia/batch', [AsistenciaController::class, 'storeBatch']);

// Routes for Students (primarily for Arduino integration)
// Note: '/students' should ideally be '/api/students' if using api.php,
// but if your Arduino calls it directly without '/api/', ensure your web.php handles it.
Route::post('/students', [StudentController::class, 'store']); // Used for creating a student via API if needed
Route::get('/students-list', [StudentController::class, 'getStudentsList']); // API route for Arduino to fetch student list