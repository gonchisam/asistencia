<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante; // Import the Estudiante model
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $asistencias = Asistencia::orderBy('fecha_hora', 'desc')->paginate(10);
        
        // Fetch all students to pass to the view, keyed by UID for easy lookup
        $estudiantes = Estudiante::all()->keyBy('uid'); 
                               
        return view('dashboard', compact('asistencias', 'estudiantes'));
    }
}