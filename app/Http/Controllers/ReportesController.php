<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
// use App\Models\Estudiante; // Ya no se necesita cargar todos aquí
use App\Models\Curso;      
use App\Models\Materia;    
use App\Exports\AsistenciasExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;      

class ReportesController extends Controller
{
    /**
     * Muestra la vista de reportes con filtros y resultados (paginados o completos).
     */
    public function index(Request $request)
    {
        // 1. Definir los filtros activos
        $filters = $request->only([
            'fecha_desde', 'fecha_fin', 'carrera', 'año', 
            'materia_id', 'paralelo', 'ci', 'modo', 'estado_llegada' 
            // Se quitó 'estudiante_id' y 'accion'
        ]);
        
        // 2. Aplicar filtros a la consulta (usando el método helper)
        $query = $this->applyFilters(Asistencia::query(), $request);

        // 3. Lógica de Paginación vs. Lista Larga
        $isFiltered = count(array_filter($filters)) > 0;

        if ($isFiltered) {
            $asistencias = $query->get(); // Obtener todos si hay filtros
        } else {
            $asistencias = $query->paginate(15)->withQueryString(); // Paginar si no hay filtros
        }

        // 4. Cargar datos para los <select> del formulario
        // (Ya no cargamos $estudiantes)
        $carreras = \App\Models\Estudiante::select('carrera')->distinct()->whereNotNull('carrera')->orderBy('carrera')->pluck('carrera');
        $años = \App\Models\Estudiante::select('año')->distinct()->whereNotNull('año')->orderBy('año')->pluck('año');
        $materias = Materia::orderBy('nombre')->get(['id', 'nombre']);
        $paralelos = Curso::select('paralelo')->distinct()->whereNotNull('paralelo')->orderBy('paralelo')->pluck('paralelo'); // <-- NUEVO

        // 5. Retornar la vista
        return view('reportes.index', compact(
            'asistencias', 
            'carreras', 
            'años', 
            'materias', 
            'paralelos', // <-- NUEVO
            'isFiltered'
        ));
    }

    /**
     * Método helper para aplicar filtros a la consulta de Asistencia.
     */
    private function applyFilters($query, Request $request)
    {
        // Cargar relaciones necesarias para filtros y visualización
        $query->with('estudiante', 'curso.materia'); 

        // Filtros directos en la tabla 'asistencias'
        $query->when($request->filled('fecha_desde'), function ($q) use ($request) {
            try { // Usar fecha_hora
                $q->where('fecha_hora', '>=', Carbon::parse($request->input('fecha_desde'))->startOfDay());
            } catch (\Exception $e) {}
        });
        $query->when($request->filled('fecha_fin'), function ($q) use ($request) {
            try { // Usar fecha_hora
                $q->where('fecha_hora', '<=', Carbon::parse($request->input('fecha_fin'))->endOfDay());
            } catch (\Exception $e) {}
        });
        $query->when($request->filled('modo'), function ($q) use ($request) {
            $q->where('modo', $request->input('modo'));
        });
        $query->when($request->filled('estado_llegada'), function ($q) use ($request) { // <-- NUEVO
            $q->where('estado_llegada', $request->input('estado_llegada'));
        });
        // Se eliminó el filtro 'accion'

        // Filtros en la relación 'estudiante'
        $query->whereHas('estudiante', function ($q) use ($request) {
            // Se eliminó estudiante_id
            $q->when($request->filled('carrera'), function ($sq) use ($request) {
                $sq->where('carrera', $request->input('carrera'));
            });
            $q->when($request->filled('año'), function ($sq) use ($request) { // <-- Usar 'año' consistentemente
                $sq->where('año', $request->input('año'));
            });
            $q->when($request->filled('ci'), function ($sq) use ($request) {
                $sq->where('ci', 'like', '%' . $request->input('ci') . '%');
            });
        });

        // Filtros en la relación 'curso' (Materia y Paralelo)
        $query->whereHas('curso', function ($q) use ($request) {
             $q->when($request->filled('materia_id'), function ($sq) use ($request) { // <-- NUEVO (filtrar por materia_id en curso)
                $sq->where('materia_id', $request->input('materia_id'));
             });
             $q->when($request->filled('paralelo'), function ($sq) use ($request) { // <-- NUEVO
                $sq->where('paralelo', $request->input('paralelo'));
             });
        });

        // Ordenar siempre por el más reciente
        return $query->latest('fecha_hora'); // Usar fecha_hora para ordenar
    }

    /**
     * Genera el reporte en PDF.
     */
    public function generatePdf(Request $request)
    {
        // Obtener todos los resultados filtrados
        $asistencias = $this->applyFilters(Asistencia::query(), $request)->get();
        
        // Pasar los datos y los filtros a la vista PDF
        $pdf = Pdf::loadView('reportes.asistencias_pdf', compact('asistencias', 'request'));

        return $pdf->stream('reporte-asistencias-' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Genera el reporte en Excel.
     */
    public function generateExcel(Request $request)
    {
        // Pasar los filtros (como array) a la clase de exportación
        return Excel::download(
            new AsistenciasExport($request->all()), // <-- Pasamos $request->all()
            'reporte-asistencias-' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}