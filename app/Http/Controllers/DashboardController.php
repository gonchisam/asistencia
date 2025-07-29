<?php

namespace App\Http\Controllers;

use App\Models\Asistencia; // Asegúrate de importar tu modelo Asistencia
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Ejemplo: Obtener las últimas asistencias paginadas
        $asistencias = Asistencia::latest()->paginate(10);
        return view('dashboard', compact('asistencias'));
    }
}