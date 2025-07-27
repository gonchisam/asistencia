<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\StudentController;

Route::post('/asistencia', [AsistenciaController::class, 'store']);
Route::post('/asistencia/batch', [AsistenciaController::class, 'storeBatch']); // Nueva ruta
Route::post('/students', [StudentController::class, 'store']); // No necesita name() si solo es para API
Route::get('/students-list', [StudentController::class, 'getStudentsList']); // New API route for Arduino to fetch student list