<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asistencia; // Asegúrate de importar tu modelo Asistencia
use App\Models\Estudiante; // Asegúrate de importar tu modelo Estudiante
use Maatwebsite\Excel\Facades\Excel; // Importa el Facade de Excel
use Barryvdh\DomPDF\Facade\Pdf; // Importa el Facade de PDF
use App\Exports\AsistenciasExport; // Crearemos esta clase más adelante

class ReportesController extends Controller
{
    /**
     * Muestra la interfaz principal de reportes.
     */
    public function index(Request $request)
    {
        // Puedes pasar datos iniciales a la vista si es necesario
        $estudiantes = Estudiante::orderBy('nombre')->get();
        return view('reportes.index', compact('estudiantes'));
    }

    /**
     * Genera un reporte de asistencias y lo devuelve como PDF.
     */
    public function generatePdf(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estudiante_id' => 'nullable|exists:students,id',
            'accion' => 'nullable|in:ENTRADA,SALIDA',
            'modo' => 'nullable|in:WIFI,SD',
        ]);

        $query = Asistencia::with('estudiante'); // Carga la relación si la tienes definida en el modelo Asistencia

        if ($request->filled('fecha_inicio')) {
            $query->where('fecha_hora', '>=', $request->fecha_inicio . ' 00:00:00');
        }
        if ($request->filled('fecha_fin')) {
            $query->where('fecha_hora', '<=', $request->fecha_fin . ' 23:59:59');
        }
        if ($request->filled('estudiante_id')) {
            $estudiante = Estudiante::find($request->estudiante_id);
            if ($estudiante) {
                $query->where('uid', $estudiante->uid);
            }
        }
        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }
        if ($request->filled('modo')) {
            $query->where('modo', $request->modo);
        }

        $asistencias = $query->orderBy('fecha_hora', 'desc')->get();

        // Cargar los datos a la vista que será renderizada como PDF
        $pdf = Pdf::loadView('reportes.asistencias_pdf', compact('asistencias', 'request'));

        // Opcional: Establecer el tamaño de página y orientación si es necesario
        // $pdf->setPaper('a4', 'landscape');

        // Retornar la descarga del PDF o mostrarlo en el navegador
        return $pdf->download('reporte_asistencias_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Genera un reporte de asistencias y lo devuelve como archivo Excel.
     */
    public function generateExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estudiante_id' => 'nullable|exists:students,id',
            'accion' => 'nullable|in:ENTRADA,SALIDA',
            'modo' => 'nullable|in:WIFI,SD',
        ]);

        // La lógica de filtrado se pasa a la clase Exportación
        return Excel::download(new AsistenciasExport($request->all()), 'reporte_asistencias_' . now()->format('Ymd_His') . '.xlsx');
    }
}