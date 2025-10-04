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
        $estudiantes = Estudiante::orderBy('nombre')->get();
        
        // Aplica los filtros y obtén los datos para la vista previa
        $asistencias = $this->applyFilters(Asistencia::query(), $request)->get();
        
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
        // Todos los filtros de la tabla 'estudiantes' deben estar aquí
        $query->whereHas('estudiante', function ($q) use ($request) {
            if ($request->filled('estudiante_id')) {
                $q->where('id', $request->input('estudiante_id'));
            }
            if ($request->filled('carrera')) {
                $q->where('carrera', $request->input('carrera'));
            }
            // ¡CORRECCIÓN! Cambiar 'anio_estudio' a 'año'
            if ($request->filled('anio_estudio')) {
                $q->where('año', $request->input('anio_estudio'));
            }
            if ($request->filled('ci')) {
                $q->where('ci', 'like', '%' . $request->input('ci') . '%');
            }
        });
        
        return $query->orderBy('created_at', 'desc');
    }

    public function generatePdf(Request $request)
    {
        $asistencias = $this->applyFilters(Asistencia::query(), $request)->get();
        // Pass the entire $request object to the view
        $pdf = Pdf::loadView('reportes.asistencias_pdf', compact('asistencias', 'request'));
        return $pdf->download('reporte-asistencias.pdf');
    }

    public function generateExcel(Request $request)
    {
        return Excel::download(new AsistenciasExport($request->all()), 'asistencias.xlsx');
    }
}