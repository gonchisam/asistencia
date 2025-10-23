<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Estudiante;
use App\Exports\AsistenciasExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportesController extends Controller
{
    /**
     * Display a listing of the resource with filters.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Obtiene todos los estudiantes para llenar el dropdown de filtros
        $estudiantes = Estudiante::orderBy('nombre')->get();

        // --- INICIO DE LA MODIFICACIÓN (PAGINACIÓN) ---

        // 1. Preparar la consulta aplicando todos los filtros
        $query = $this->applyFilters(Asistencia::query(), $request);

        // 2. Paginar los resultados, mostrando 10 por página.
        //    Esto reemplaza a take(10) y get() usados en el intento anterior.
        $asistencias = $query->paginate(10);

        // 3. ¡MUY IMPORTANTE! 
        //    Añadir los filtros actuales a los enlaces de paginación.
        //    Sin esto, al hacer clic en la "página 2", se perderían los filtros.
        //    Si no hay filtros, solo añade la página.
        $asistencias->appends($request->all());

        // --- FIN DE LA MODIFICACIÓN ---

        return view('reportes.index', compact('estudiantes', 'asistencias'));
    }

    /**
     * A private helper method to apply all report filters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyFilters($query, Request $request)
    {
        // Filtros basados en la tabla de Asistencia
        if ($request->filled('fecha_inicio')) {
            $query->where('created_at', '>=', $request->input('fecha_inicio') . ' 00:00:00');
        }
        if ($request->filled('fecha_fin')) {
            $query->where('created_at', '<=', $request->input('fecha_fin') . ' 23:59:59');
        }
        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }
        if ($request->filled('modo')) {
            $query->where('modo', $request->input('modo'));
        }

        // Aplica los filtros a la relación 'estudiante' usando whereHas
        $query->whereHas('estudiante', function ($q) use ($request) {
            if ($request->filled('estudiante_id')) {
                $q->where('id', $request->input('estudiante_id'));
            }
            if ($request->filled('carrera')) {
                $q->where('carrera', $request->input('carrera'));
            }
            if ($request->filled('anio_estudio')) {
                // ¡CORRECCIÓN APLICADA! (Cambiado a 'año' como en tu análisis)
                $q->where('año', $request->input('anio_estudio'));
            }
            if ($request->filled('ci')) {
                $q->where('ci', 'like', '%' . $request->input('ci') . '%');
            }
        });
        
        // Asegura que siempre esté la relación cargada
        $query->with('estudiante');

        // Ordena por fecha de creación descendente (más reciente primero)
        return $query->orderBy('created_at', 'desc');
    }

    public function generatePdf(Request $request)
    {
        // Para la generación de reportes PDF/Excel, siempre se obtienen todos los resultados
        // que cumplan con el filtro (no paginados).
        $asistencias = $this->applyFilters(Asistencia::query(), $request)->get();
        
        $pdf = Pdf::loadView('reportes.asistencias_pdf', compact('asistencias', 'request'));

        // Se usa stream() para previsualizar en el navegador
        return $pdf->stream('reporte-asistencias.pdf');
    }

    public function generateExcel(Request $request)
    {
        // Para la generación de Excel, se pasan los filtros a la clase de exportación
        return Excel::download(new AsistenciasExport($request->all()), 'asistencias.xlsx');
    }
}