<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Models\Curso;      
use App\Models\Materia;    
use Illuminate\Http\Request;
use Carbon\Carbon;      

class DashboardController extends Controller
{
    // Tu método index() se queda exactamente igual
    public function index(Request $request)
    {
        // 1. Definir los filtros
        $filters = $request->only([
            'uid', 'carrera', 'año', 'fecha_desde', 'fecha_hasta', 
            'curso_id', 'materia_id', 'modo', 'estado_llegada'
        ]);

        // 2. Iniciar la consulta
        $query = Asistencia::with('estudiante', 'curso.materia', 'periodo');

        // ... (toda tu lógica de filtros when() y whereHas() va aquí) ...
        // Búsqueda por UID
        $query->when($request->filled('uid'), function ($q) use ($request) {
            $q->where('uid', 'like', '%' . $request->input('uid') . '%');
        });
        // Búsqueda por Modo de Asistencia
        $query->when($request->filled('modo'), function ($q) use ($request) {
            $q->where('modo', $request->input('modo'));
        });
        // Búsqueda por Estado de Llegada
        $query->when($request->filled('estado_llegada'), function ($q) use ($request) {
            $q->where('estado_llegada', $request->input('estado_llegada'));
        });
        // Búsqueda por Rango de Fechas (Desde)
        $query->when($request->filled('fecha_desde'), function ($q) use ($request) {
            try {
                $fechaDesde = Carbon::parse($request->input('fecha_desde'))->startOfDay();
                $q->where('fecha_hora', '>=', $fechaDesde);
            } catch (\Exception $e) {}
        });
        // Búsqueda por Rango de Fechas (Hasta)
        $query->when($request->filled('fecha_hasta'), function ($q) use ($request) {
            try {
                $fechaHasta = Carbon::parse($request->input('fecha_hasta'))->endOfDay();
                $q->where('fecha_hora', '<=', $fechaHasta);
            } catch (\Exception $e) {}
        });
        // Búsqueda por Curso
        $query->when($request->filled('curso_id'), function ($q) use ($request) {
            $q->where('curso_id', $request->input('curso_id'));
        });
        // Filtros en 'estudiante'
        $query->whereHas('estudiante', function ($q) use ($request) {
            $q->when($request->filled('carrera'), function ($sq) use ($request) {
                $sq->where('carrera', $request->input('carrera'));
            });
            $q->when($request->filled('año'), function ($sq) use ($request) {
                $sq->where('año', $request->input('año'));
            });
        });
        // Filtros en 'curso.materia'
        $query->when($request->filled('materia_id'), function ($q) use ($request) {
            $q->whereHas('curso', function ($sq) use ($request) {
                $sq->where('materia_id', $request->input('materia_id'));
            });
        });

        // 3. Lógica de Visualización
        $isFiltered = count(array_filter($filters)) > 0;
        $query->latest('fecha_hora');

        if ($isFiltered) {
            $asistencias = $query->get();
        } else {
            $asistencias = $query->paginate(15)->withQueryString();
        }

        // 4. Cargar datos para los <select>
        $carreras = Estudiante::select('carrera')->distinct()->orderBy('carrera')->pluck('carrera');
        $años = Estudiante::select('año')->distinct()->orderBy('año')->pluck('año');
        $cursos_query = Curso::with('materia');
        $cursos = $cursos_query->get()->sortBy(function($curso) {
            return $curso->materia->nombre ?? ''; 
        });
        $materias = Materia::orderBy('nombre')->get(['id', 'nombre']);

        // 5. Retornar la vista principal
        return view('dashboard', compact(
            'asistencias', 'carreras', 'años', 'cursos', 'materias', 'isFiltered'
        ));
    }

    // --- ¡NUEVO MÉTODO PARA POLLING! ---
    public function fetchAsistenciaTabla(Request $request)
    {
        // 1. Definir los filtros (copiado de index)
        $filters = $request->only([
            'uid', 'carrera', 'año', 'fecha_desde', 'fecha_hasta', 
            'curso_id', 'materia_id', 'modo', 'estado_llegada'
        ]);

        // 2. Iniciar la consulta (copiado de index)
        $query = Asistencia::with('estudiante', 'curso.materia', 'periodo');

        // ... (toda tu lógica de filtros when() y whereHas() va aquí) ...
        // Búsqueda por UID
        $query->when($request->filled('uid'), function ($q) use ($request) {
            $q->where('uid', 'like', '%' . $request->input('uid') . '%');
        });
        // Búsqueda por Modo de Asistencia
        $query->when($request->filled('modo'), function ($q) use ($request) {
            $q->where('modo', $request->input('modo'));
        });
        // Búsqueda por Estado de Llegada
        $query->when($request->filled('estado_llegada'), function ($q) use ($request) {
            $q->where('estado_llegada', $request->input('estado_llegada'));
        });
        // Búsqueda por Rango de Fechas (Desde)
        $query->when($request->filled('fecha_desde'), function ($q) use ($request) {
            try {
                $fechaDesde = Carbon::parse($request->input('fecha_desde'))->startOfDay();
                $q->where('fecha_hora', '>=', $fechaDesde);
            } catch (\Exception $e) {}
        });
        // Búsqueda por Rango de Fechas (Hasta)
        $query->when($request->filled('fecha_hasta'), function ($q) use ($request) {
            try {
                $fechaHasta = Carbon::parse($request->input('fecha_hasta'))->endOfDay();
                $q->where('fecha_hora', '<=', $fechaHasta);
            } catch (\Exception $e) {}
        });
        // Búsqueda por Curso
        $query->when($request->filled('curso_id'), function ($q) use ($request) {
            $q->where('curso_id', $request->input('curso_id'));
        });
        // Filtros en 'estudiante'
        $query->whereHas('estudiante', function ($q) use ($request) {
            $q->when($request->filled('carrera'), function ($sq) use ($request) {
                $sq->where('carrera', $request->input('carrera'));
            });
            $q->when($request->filled('año'), function ($sq) use ($request) {
                $sq->where('año', $request->input('año'));
            });
        });
        // Filtros en 'curso.materia'
        $query->when($request->filled('materia_id'), function ($q) use ($request) {
            $q->whereHas('curso', function ($sq) use ($request) {
                $sq->where('materia_id', $request->input('materia_id'));
            });
        });

        // 3. Lógica de Visualización (copiado de index)
        $isFiltered = count(array_filter($filters)) > 0;
        $query->latest('fecha_hora');

        if ($isFiltered) {
            $asistencias = $query->get();
        } else {
            $asistencias = $query->paginate(15)->withQueryString();
        }

        // 4. ¡LA ÚNICA DIFERENCIA! Retornar la vista PARCIAL
        return view('partials._asistencia-tabla', compact(
            'asistencias', 
            'isFiltered'
        ));
    }
}