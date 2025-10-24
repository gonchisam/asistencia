<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante; // Importa el modelo Estudiante
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Inicia la consulta de Asistencia, cargando la relación 'estudiante'
        $query = Asistencia::with('estudiante');

        // --- Aplicar Filtros ---

        // 1. Filtrar por UID (del formulario existente)
        if ($request->filled('uid')) {
            $query->where('uid', 'like', '%' . $request->input('uid') . '%');
        }

        // 2. Filtrar por Carrera (Nuevo)
        if ($request->filled('carrera')) {
            // whereHas filtra Asistencia basado en una condición en la relación 'estudiante'
            $query->whereHas('estudiante', function ($q) use ($request) {
                $q->where('carrera', $request->input('carrera'));
            });
        }

        // 3. Filtrar por Año (Nuevo)
        if ($request->filled('año')) {
            $query->whereHas('estudiante', function ($q) use ($request) {
                $q->where('año', $request->input('año'));
            });
        }

        // --- Fin Filtros ---

        // Obtener resultados paginados, ordenados por el más reciente
        // withQueryString() asegura que la paginación mantenga los filtros aplicados
        $asistencias = $query->latest('fecha_hora')->paginate(10)->withQueryString();

        // Obtener valores únicos para los dropdowns de los filtros
        $carreras = Estudiante::select('carrera')->distinct()->orderBy('carrera')->pluck('carrera');
        $años = Estudiante::select('año')->distinct()->orderBy('año')->pluck('año');

        // Retornar la vista con las variables
        return view('dashboard', compact('asistencias', 'carreras', 'años'));
    }
}